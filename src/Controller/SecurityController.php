<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserForm;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/registration", name="security_registration", methods={"POST","GET"})
     */
    public function registration(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        //dd($request);

        try {
            // Create form and handle data
            $user = new User();
            $form = $this->createJsonForm(UserForm::class, $user);
            $this->handleJsonForm($form, $request);
            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPlainPassword()));

            // Save user entity
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
        } catch (\Exception $e) {
            throw $e;
            //dd($e);
        }


        // Return new user access token
        return $this->json(['OK']);
    }
}