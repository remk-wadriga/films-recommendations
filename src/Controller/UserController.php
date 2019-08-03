<?php


namespace App\Controller;

use App\Form\UserForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="get_user_info", methods={"GET"})
     */
    public function view()
    {
        $user = $this->getUser();

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'age' => $user->getAge(),
            'sex' => $user->getSex(),
            'aboutMe' => $user->getAboutMe(),
        ]);
    }

    /**
     * @Route("/account", name="update_user_info", methods={"PUT"})
     */
    public function update(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = $this->getUser();
        $form = $this->createJsonForm(UserForm::class, $user, ['action' => UserForm::ACTION_UPDATE]);
        $this->handleJsonForm($form, $request);
        if ($user->getPlainPassword()) {
            $user->setPassword($passwordEncoder->encodePassword($user, $user->getPlainPassword()));
        }

        // Save user entity
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        // Return new user access token
        return $this->json(['OK']);
    }
}