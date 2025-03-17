<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->getIsArchived()) {
            // L'utilisateur est archivé, on empêche la connexion
            throw new CustomUserMessageAccountStatusException(
                'Votre compte utilisateur a été archivé. Veuillez contacter l\'administrateur pour le réactiver.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Vous pouvez ajouter d'autres vérifications post-authentification ici si nécessaire
    }
}