<?php
/**
 * Created by PhpStorm.
 * User: Dmitry
 * Date: 07.09.2018
 * Time: 01:58
 */

namespace App\Security;

use App\Entity\User;
use App\Helpers\AccessTokenEntityInterface;
use App\Helpers\AccessTokenHelper;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AccessTokenUserProvider implements UserProviderInterface
{
    private $em;
    private $container;

    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    /**
     * @param string $username
     * @return User|null
     */
    public function loadUserByUsername($username)
    {
        /** @var UserRepository $repository */
        $repository = $this->em->getRepository(User::class);
        return $repository->findOneBy(['email' => $username]);
    }

    public function loadUserByAccessToken(string $accessToken): ?AccessTokenEntityInterface
    {
        /** @var UserRepository $repository */
        $repository = $this->em->getRepository(User::class);
        return $repository->findOneBy(['accessToken' => $accessToken]);
    }

    public function refreshUser(UserInterface $user)
    {
        return $user;
    }

    public function supportsClass($class)
    {
        // TODO: Implement supportsClass() method.
    }

    /**
     * Create an access token object with successfully authenticated user credentials
     * This method should run only if user is authenticated correctly!
     *
     * @param AccessTokenEntityInterface $user
     * @return \App\Security\AccessToken
     */
    public function createAccessToken(AccessTokenEntityInterface $user): AccessToken
    {
        try {
            $this->em->persist($user);
            $user
                ->setAccessToken(AccessTokenHelper::generateAccessToken($user))
                ->setRenewToken(AccessTokenHelper::generateAccessToken($user))
                ->setAccessTokenExpiredAt(AccessTokenHelper::getAccessTokenExpiredAt());
            $this->em->flush();
        } catch (ORMException $e) {
            throw new AuthenticationException(sprintf('Can`t create token for user: %s', $e->getMessage()), null, $e);
        }

        return $this->getAccessTokenForUser($user);
    }

    /**
     * Just create new AccessToken instance for successfully authenticated user
     *
     * @param AccessTokenEntityInterface $user
     * @return AccessToken
     */
    public function getAccessTokenForUser(AccessTokenEntityInterface $user): AccessToken
    {
        $token = new AccessToken();
        $token->setUser($user);
        if ($this->container->hasParameter('frontend_date_time_format')) {
            $token->setDateTimeFormat($this->container->getParameter('frontend_date_time_format'));
        }
        return $token;
    }
}