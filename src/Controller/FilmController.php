<?php


namespace App\Controller;

use App\Entity\Film;
use App\Service\FilmService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class FilmController extends AbstractController
{
    private $filmService;

    public function __construct(FilmService $filmService)
    {
        $this->filmService = $filmService;
    }

    /**
     * @Route("/films", name="get_films_list", methods={"GET"})
     */
    public function list()
    {
        $films = $this->filmService->getRecommendedForUser($this->getUser());
        return $this->json($this->toApi($films));
    }

    /**
     * @Route("/film", name="create_films", methods={"POST"})
     */
    public function create(Request $request)
    {
        dd($request->getContent());
    }


    /**
     * @param Film[] $films
     * @return array
     */
    private function toApi($films)
    {
        $params = [];

        foreach ($films as $film) {
            dd($film);
        }

        return $params;
    }
}