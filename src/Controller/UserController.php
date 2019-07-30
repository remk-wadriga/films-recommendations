<?php


namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="user_info", methods={"GET"})
     */
    public function logout()
    {
        $user = $this->getUser();

        return $this->json([
            'username' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'age' => $user->getAge(),
            'sex' => $user->getSex()
        ]);
    }
}