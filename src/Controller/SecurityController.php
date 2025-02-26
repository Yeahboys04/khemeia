<?php

namespace App\Controller;

use App\Entity\Site;
use App\Entity\Status;
use App\Entity\User;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use phpCAS;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/login/{force_authentification}', name: 'login', methods: ['GET','HEAD'])]
    public function index(
        string $force_authentification,
        Request $request,
        MailerInterface $mailer,
        ValidatorInterface $validator
    ): Response {
        include_once('CAS/CAS.php');
        include_once('cas.sso');

        phpCAS::client(CAS_VERSION_2_0, $serveurSSO, $serveurSSOPort, $serveurSSORacine, true);
        phpCAS::setLang(PHPCAS_LANG_ENGLISH);
        phpCAS::setNoCasServerValidation();
        phpCAS::handleLogoutRequests(false);

        if (!phpCAS::checkAuthentication() && isset($force_authentification)) {
            phpCAS::forceAuthentication();
        }

        $user = phpCAS::getUser();
        $attributes = phpCAS::getAttributes();

        if ($user !== null) {
            $entityManager = $this->getDoctrine()->getManager();
            $repositoryUser = $entityManager->getRepository(User::class);
            $finduser = $repositoryUser->findByUsername($user);

            if ($finduser) {
                $token = new UsernamePasswordToken($finduser[0], null, 'main', $finduser[0]->getRoles());
                $this->container->get('security.token_storage')->setToken($token);
                $this->container->get('session')->set('_security_main', serialize($token));
                return $this->redirectToRoute('home_page');
            }

            $repositoryStatus = $entityManager->getRepository(Status::class);
            $status = $repositoryStatus->find(1);
            $repositorySite = $entityManager->getRepository(Site::class);
            $site = $repositorySite->find(4);

            $newUser = new User();
            $newUser->setUsername($user);
            $newUser->setFullname($attributes["displayname"]);
            $newUser->setMail($attributes["mail"]);
            $newUser->setIdStatus($status);
            $newUser->setIdSite($site);

            $errors = $validator->validate($newUser);
            if (count($errors) > 0) {
                return $this->render('security/login.html.twig', [
                    'force_authentication' => $force_authentification,
                    'user' => $user,
                    'errors' => $errors
                ]);
            }

            $entityManager->persist($newUser);
            $entityManager->flush();

            $administrators = $repositoryUser->loadAdministrators();
            $mails = array_map(fn($admin) => $admin->getMail(), $administrators);

            $email = (new Email())
                ->from('contact@khemeia.fr')
                ->to(...$mails)
                ->subject('Khemeia - Nouvelle connexion')
                ->html($this->renderView('emails/registration.html.twig', [
                    'user' => $newUser,
                    'date' => new DateTime()
                ]));

            $mailer->send($email);

            $token = new UsernamePasswordToken($newUser, null, 'main', $newUser->getRoles());
            $this->container->get('security.token_storage')->setToken($token);
            $this->container->get('session')->set('_security_main', serialize($token));

            return $this->redirectToRoute('home_page');
        }

        return $this->render('security/login.html.twig', [
            'force_authentication' => $force_authentification,
            'user' => $user
        ]);
    }
}
