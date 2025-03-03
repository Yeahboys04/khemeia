<?php
// src/Controller/SimpleCasController.php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Security\LoginFormAuthenticator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SimpleCasController extends AbstractController
{
    private $entityManager;
    private $params;

    public function __construct(EntityManagerInterface $entityManager, ParameterBagInterface $params)
    {
        $this->entityManager = $entityManager;
        $this->params = $params;
    }

    #[Route('/cas-login', name: 'app_cas_login')]
    public function casLogin(Request $request, UserAuthenticatorInterface $userAuthenticator, LoginFormAuthenticator $authenticator): Response
    {
        // Récupérer les paramètres CAS
        $casServer = $this->params->get('cas.server');
        $casPort = $this->params->get('cas.port');
        $casUri = $this->params->get('cas.uri');

        // Construire l'URL de base du serveur CAS
        $casBaseUrl = 'https://' . $casServer;
        if ($casPort != 443) {
            $casBaseUrl .= ':' . $casPort;
        }
        if (!empty($casUri)) {
            $casBaseUrl .= $casUri;
        }

        // 1. Vérifier si un ticket est présent dans la requête (retour du serveur CAS)
        $ticket = $request->query->get('ticket');

        if (!$ticket) {
            // Pas de ticket => rediriger vers CAS pour s'authentifier

            // Construire l'URL de service (l'URL de retour)
            $serviceUrl = $this->generateUrl('app_cas_login', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);

            // Construire l'URL de redirection vers le serveur CAS
            $casLoginUrl = $casBaseUrl . '/login?service=' . urlencode($serviceUrl);

            // Rediriger l'utilisateur vers CAS
            return $this->redirect($casLoginUrl);
        }

        // 2. Un ticket est présent => valider le ticket auprès du serveur CAS
        $serviceUrl = $this->generateUrl('app_cas_login', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
        $validateUrl = $casBaseUrl . '/serviceValidate?ticket=' . $ticket . '&service=' . urlencode($serviceUrl);

        try {
            // Appeler le serveur CAS pour valider le ticket
            $client = HttpClient::create();
            $response = $client->request('GET', $validateUrl);
            $content = $response->getContent();

            // Analyser la réponse XML pour extraire le nom d'utilisateur
            if (strpos($content, '<cas:authenticationSuccess>') !== false) {
                // Le ticket est valide, extraire le nom d'utilisateur
                preg_match('/<cas:user>(.*?)<\/cas:user>/', $content, $matches);

                if (isset($matches[1])) {
                    $casUsername = $matches[1];

                    // 3. Rechercher l'utilisateur dans la base de données
                    $userRepository = $this->entityManager->getRepository(User::class);
                    $user = $userRepository->findOneBy(['username' => $casUsername]);

                    if (!$user) {
                        // L'utilisateur n'existe pas dans la base de données
                        return $this->render('security/cas_error.html.twig', [
                            'message' => "L'utilisateur '$casUsername' n'existe pas dans la base de données."
                        ]);
                    }

                    // 4. Connecter l'utilisateur avec Symfony Security
                    return $userAuthenticator->authenticateUser($user, $authenticator, $request);
                }
            }

            // Si on arrive ici, la validation a échoué
            return $this->render('security/cas_error.html.twig', [
                'message' => 'Échec de la validation du ticket CAS. Réponse du serveur: ' . htmlspecialchars($content)
            ]);

        } catch (\Exception $e) {
            return $this->render('security/cas_error.html.twig', [
                'message' => 'Erreur lors de la validation du ticket: ' . $e->getMessage()
            ]);
        }
    }

    #[Route('/cas-logout', name: 'app_cas_logout')]
    public function casLogout(): Response
    {
        // Récupérer les paramètres CAS
        $casServer = $this->params->get('cas.server');
        $casPort = $this->params->get('cas.port');
        $casUri = $this->params->get('cas.uri');

        // Construire l'URL de base du serveur CAS
        $casBaseUrl = 'https://' . $casServer;
        if ($casPort != 443) {
            $casBaseUrl .= ':' . $casPort;
        }
        if (!empty($casUri)) {
            $casBaseUrl .= $casUri;
        }

        // Construire l'URL de service (l'URL de retour après déconnexion)
        $serviceUrl = $this->generateUrl('app_login', [], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);

        // Construire l'URL de déconnexion CAS
        $casLogoutUrl = $casBaseUrl . '/logout?service=' . urlencode($serviceUrl);

        // Rediriger l'utilisateur vers la déconnexion CAS
        return $this->redirect($casLogoutUrl);
    }
}