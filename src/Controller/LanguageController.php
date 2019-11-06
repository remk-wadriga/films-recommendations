<?php


namespace App\Controller;

use App\Entity\Language;
use Symfony\Component\Routing\Annotation\Route;

class LanguageController extends AbstractController
{
    /**
     * @Route("/languages", name="languages_list", methods={"GET"})
     */
    public function list()
    {
        $languages = $this->getDoctrine()->getRepository(Language::class)->findAll();
        return $this->json($this->getItemsList($languages));
    }
}