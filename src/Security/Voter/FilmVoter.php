<?php

namespace App\Security\Voter;

use App\Entity\Film;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class FilmVoter extends Voter
{
    const ACTION_MANAGE = 'MANAGE';
    const ACTION_DELETE = 'DELETE';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected static function getAllowedActions(): array
    {
        return [
            self::ACTION_MANAGE,
            self::ACTION_DELETE,
        ];
    }

    protected function supports($attribute, $subject)
    {
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, self::getAllowedActions()) && $subject instanceof Film;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::ACTION_MANAGE:
            case self::ACTION_DELETE:
                return $this->security->isGranted('ROLE_ADMIN', $user) || $subject->getUser() == $user;
                break;
        }

        return false;
    }

}