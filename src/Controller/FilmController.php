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
     * @Route("/films", name="films_list", methods={"GET"})
     */
    public function list()
    {
        $films = $this->filmService->getRecommendedForUser($this->getUser());
        return $this->json($this->toApi($films));
    }

    /**
    * @Route("/film/{id}", name="film_view", methods={"GET"})
    */
    public function view(Film $film)
    {
        $filmData = $this->toApi([$film]);
        return $this->json($filmData[0]);
    }

    /**
     * @Route("/film", name="film_create", methods={"POST"})
     */
    public function create(Request $request)
    {
        dd($request->getContent());
    }

    /**
     * @Route("/film/{id}", name="film_update", methods={"PUT"})
     */
    public function update(Film $film, Request $request)
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