<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\LoginFormAuthenticator;
use phpCAS;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
// Ajoutez cette ligne pour importer la classe CasWrapper
use App\Service\CasWrapper;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class SecurityController extends AbstractController
{
    private ParameterBagInterface $params;
    private RequestStack $requestStack;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ParameterBagInterface $params,
        RequestStack $requestStack,
        EntityManagerInterface $entityManager
    ) {
        $this->params = $params;
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastAuthenticationError();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Cette méthode peut rester vide - elle sera interceptée par la clé logout de votre pare-feu
        throw new \LogicException('Cette méthode ne devrait jamais être appelée directement.');
    }

    #[Route('/cas-login/{force_authentification}', name: 'app_cas_login', defaults: ['force_authentification' => null], methods: ['GET'])]
    public function casLogin(    Request $request,
                                 Security $security,
                                 UserAuthenticatorInterface $userAuthenticator,
                                 LoginFormAuthenticator $authenticator,
                                 string $force_authentification = null): Response
    {
        try {
            // Activer l'affichage des erreurs pour le débogage
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);

            // Récupération des paramètres CAS
            $serveurSSO = $this->params->get('cas.server');
            $serveurSSOPort = $this->params->get('cas.port');
            $serveurSSORacine = $this->params->get('cas.uri');
            $fixedServiceUrl = $this->params->get('cas.fixed_service_url');

            // Vérifier les paramètres
            $debugInfo = "Paramètres CAS: serveur=$serveurSSO, port=$serveurSSOPort, racine=$serveurSSORacine, url=$fixedServiceUrl<br>";

            // Charger phpCAS avec précaution
            if (!class_exists('phpCAS')) {
                // Essayer plusieurs chemins
                $paths = [
                    $this->getParameter('kernel.project_dir') . '/vendor/jasig/phpcas/source/CAS.php',
                    '/usr/share/php/CAS/CAS.php',
                    'CAS/CAS.php'
                ];

                $loaded = false;
                foreach ($paths as $path) {
                    if (file_exists($path)) {
                        require_once($path);
                        $loaded = true;
                        $debugInfo .= "phpCAS chargé depuis: $path<br>";
                        break;
                    }
                }

                if (!$loaded) {
                    throw new \Exception("Impossible de trouver phpCAS");
                }
            }

            // Initialiser phpCAS avec notre wrapper
            $success = CasWrapper::initCAS($serveurSSO, $serveurSSOPort, $serveurSSORacine, true);

            if (!$success) {
                throw new \Exception("Échec de l'initialisation de phpCAS");
            }

            // Essayer de définir l'URL de service
            if (!empty($fixedServiceUrl)) {
                try {
                    \phpCAS::setFixedServiceURL($fixedServiceUrl);
                    $debugInfo .= "URL de service définie: $fixedServiceUrl<br>";
                } catch (\Exception $e) {
                    $debugInfo .= "Erreur lors de la définition de l'URL de service: " . $e->getMessage() . "<br>";
                }
            }

            // À ce stade, tout devrait fonctionner, donc vérifions l'authentification
            $isAuthenticated = false;
            try {
                \phpCAS::setNoCasServerValidation();
                $isAuthenticated = \phpCAS::checkAuthentication();
                $debugInfo .= "Authentifié? " . ($isAuthenticated ? "Oui" : "Non") . "<br>";
            } catch (\Exception $e) {
                $debugInfo .= "Erreur lors de la vérification de l'authentification: " . $e->getMessage() . "<br>";
            }

            if ($isAuthenticated) {
                // L'utilisateur est authentifié via CAS
                try {
                    $casUsername = \phpCAS::getUser();
                    $debugInfo .= "Utilisateur CAS: $casUsername<br>";

                    // Rechercher l'utilisateur dans votre base de données
                    $userRepository = $this->entityManager->getRepository(User::class);
                    $user = $userRepository->findOneBy(['username' => $casUsername]);

                    if (!$user) {
                        return new Response("Utilisateur CAS non trouvé dans l'application: $casUsername<br>$debugInfo");
                    }

                    // Connecter l'utilisateur dans Symfony
                    return $userAuthenticator->authenticateUser($user, $authenticator, $request);


                } catch (\Exception $e) {
                    return new Response("Erreur après authentification: " . $e->getMessage() . "<br>$debugInfo");
                }
            } else {
                // L'utilisateur n'est pas authentifié
                if ($force_authentification !== null) {
                    try {
                        $debugInfo .= "Tentative de forcer l'authentification...<br>";
                        // Forcer l'authentification CAS pourrait rediriger, donc cette ligne pourrait ne pas être atteinte
                        \phpCAS::forceAuthentication();
                        $debugInfo .= "Redirection vers le serveur CAS...<br>";
                    } catch (\Exception $e) {
                        return new Response("Erreur lors de la tentative d'authentification forcée: " . $e->getMessage() . "<br>$debugInfo");
                    }
                }

                // Si nous sommes ici, c'est que l'authentification n'a pas été forcée ou a échoué
                return new Response("Pas d'authentification CAS. Debug: <br>$debugInfo");
            }
        } catch (\Exception $e) {
            // En cas d'erreur générale, afficher des informations de débogage
            return new Response("Erreur générale: " . $e->getMessage() . "<br>Trace: " . $e->getTraceAsString());
        }
    }

    #[Route('/cas-logout', name: 'app_cas_logout')]
    public function casLogout(): Response
    {
        // Initialisation du client CAS avec vérification si la classe existe déjà
        if (!class_exists('phpCAS')) {
            require_once('CAS/CAS.php');
        }

        // Récupération des paramètres CAS depuis le service ParameterBag
        $serveurSSO = $this->params->get('cas.server');
        $serveurSSOPort = $this->params->get('cas.port');
        $serveurSSORacine = $this->params->get('cas.uri');

        // Initialiser le client CAS
        phpCAS::client(CAS_VERSION_2_0, $serveurSSO, $serveurSSOPort, $serveurSSORacine, true);
        phpCAS::setNoCasServerValidation();

        // Déconnecter l'utilisateur du serveur CAS
        phpCAS::logout(['url' => $this->generateUrl('app_login', [], UrlGeneratorInterface::ABSOLUTE_URL)]);

        // Cette ligne ne sera jamais atteinte car phpCAS::logout() redirige l'utilisateur
        return $this->redirectToRoute('app_login');
    }

    #[Route('/cas-test', name: 'app_cas_test')]
    public function casTest(): Response
    {
        $params = [
            'serveur' => $this->params->get('cas.server'),
            'port' => $this->params->get('cas.port'),
            'racine' => $this->params->get('cas.uri'),
            'url' => $this->params->get('cas.fixed_service_url')
        ];

        return new Response('<pre>' . print_r($params, true) . '</pre>');
    }
}