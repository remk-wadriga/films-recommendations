<?php


namespace App\Controller;

use App\Entity\Genre;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GenreController extends AbstractController
{
    /**
     * @Route("/genres", name="genres_list", methods={"GET"})
     */
    public function list()
    {
        /** @var Genre[] $genres */
        $genres = $this->getDoctrine()->getRepository(Genre::class)->findAll();
        return $this->json($this->toApi($genres));
    }

    /**
     * @Route("/genre/{id}", name="genre_view", methods={"GET"})
     */
    public function view(Genre $genre)
    {
        $data = $this->toApi([$genre]);
        return $this->json($data[0]);
    }

    /**
     * @Route("/genre", name="genre_create", methods={"POST"})
     */
    public function create(Request $request)
    {
        dd($request->getContent());
    }

    /**
     * @Route("/genre/{id}", name="genre_update", methods={"PUT"})
     */
    public function update(Genre $genre, Request $request)
    {
        dd($request->getContent());
    }

    /**
     * @param Genre[] $genres
     * @return array
     */
    private function toApi($genres)
    {
        $params = [];

        foreach ($genres as $genre) {
            $params[] = [
                'id' => $genre->getId(),
                'name' => $genre->getName(),
            ];
        }

        return $params;
    }
}