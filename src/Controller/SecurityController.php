<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserForm;
use App\Security\AccessTokenUserProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class SecurityController extends AbstractController
{
    /**
     * @Route("/registration", name="security_registration", methods={"POST"})
     */
    public function registration(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        // Create form and handle data
        $user = new User();
        $form = $this->createJsonForm(UserForm::class, $user);
        $this->handleJsonForm($form, $request);
        $user->setPassword($passwordEncoder->encodePassword($user, $user->getPlainPassword()));

        // Save user entity
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        // Return new user access token
        return $this->json(['OK']);
    }

    /**
     * @Route("/login", name="security_login", methods={"POST"})
     */
    public function login(Request $request, AccessTokenUserProvider $userProvider, UserPasswordEncoderInterface $passwordEncoder)
    {
        // 1. Check is request has required params
        $username = $request->get('username');
        $password = $request->get('password');
        if (empty($username) || empty($password)) {
            throw new AuthenticationException('Params "username" and "password" area required!', Response::HTTP_UNAUTHORIZED);
        }
        // 2. Try to find user by username and if it`s found check the password
        $user = $userProvider->loadUserByUsername($username);
        if ($user === null || $passwordEncoder->isPasswordValid($user, $password) !== true) {
            throw new AuthenticationException('Username or password incorrect', Response::HTTP_UNAUTHORIZED);
        }
        // 3. Create a new user access token
        $token = $userProvider->createAccessToken($user);
        // 4. Return it
        return $this->json($token->toApi());
    }

    /**
     * @Route("/logout", name="security_logout", methods={"POST"})
     */
    public function logout(AccessTokenUserProvider $userProvider)
    {
        // Just create a new access token
        $userProvider->createAccessToken($this->getUser());
        // Say "OK"
        return $this->json('OK');
    }

    /**
     * @Route("/renew-token", name="security_renew_token", methods={"POST"})
     */
    public function renewToken(AccessTokenUserProvider $userProvider)
    {
        // Create a new access token
        $newToken = $userProvider->createAccessToken($this->getUser());
        // Return it
        return $this->json($newToken->toApi());
    }
}