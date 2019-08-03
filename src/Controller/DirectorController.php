<?php


namespace App\Controller;

use App\Entity\Director;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DirectorController extends AbstractController
{
    /**
     * @Route("/directors", name="directors_list", methods={"GET"})
     */
    public function list()
    {
        /** @var Director[] $directors */
        $directors = $this->getDoctrine()->getRepository(Director::class)->findAll();
        return $this->json($this->toApi($directors));
    }

    /**
     * @Route("/director/{id}", name="director_view", methods={"GET"})
     */
    public function view(Director $director)
    {
        $data = $this->toApi([$director]);
        return $this->json($data[0]);
    }

    /**
     * @Route("/director", name="director_create", methods={"POST"})
     */
    public function create(Request $request)
    {
        dd($request->getContent());
    }

    /**
     * @Route("/director/{id}", name="director_update", methods={"PUT"})
     */
    public function update(Director $director, Request $request)
    {
        dd($request->getContent());
    }

    /**
     * @param Director[] $directors
     * @return array
     */
    private function toApi($directors)
    {
        $params = [];

        foreach ($directors as $director) {
            $params[] = [
                'id' => $director->getId(),
                'name' => $director->getName(),
                'sex' => $director->getSex(),
                'age' => $director->getAge(),
                'country' => $director->getCountry()->getName(),
            ];
        }

        return $params;
    }
}