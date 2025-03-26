<?php

namespace App\Service;

use App\Entity\Storagecard;
use App\Entity\User;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

/**
 * Service pour l'envoi d'emails
 */
class MailService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
        private readonly Environment $twig
    ) {
    }

    /**
     * Envoie un email de demande de retrait de produit aux responsables du site
     *
     * @return bool Succès de l'envoi
     */
    public function sendRemoveRequestEmail(int $siteId, User $user, Storagecard $storagecard): bool
    {
        /** @var UserRepository $repositoryUser */
        $repositoryUser = $this->entityManager->getRepository(User::class);
        $supervisors = $repositoryUser->loadSupervisors($siteId);

        $mails = [];
        foreach ($supervisors as $supervisor) {
            $mails[] = $supervisor->getMail();
        }

        // Vérifier que des emails ont bien été récupérés
        if (empty($mails)) {
            throw new LogicException('Aucun responsable n\'a été trouvé pour recevoir l\'email.');
        }

        $emailContent = $this->twig->render('emails/askremoveproduct.html.twig', [
            'user' => $user,
            'date' => new DateTimeImmutable(),
            'storagecard' => $storagecard
        ]);

        $email = (new Email())
            ->from('contact@khemeia.fr')
            ->to(...$mails)
            ->subject('Khemeia - Demande de retrait de produit')
            ->html($emailContent);

        try {
            $this->mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            return false;
        }
    }
}