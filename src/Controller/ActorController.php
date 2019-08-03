<?php


namespace App\Controller;

use App\Entity\Actor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ActorController extends AbstractController
{
    /**
     * @Route("/actors", name="actors_list", methods={"GET"})
     */
    public function list()
    {
        /** @var Actor[] $actors */
        $actors = $this->getDoctrine()->getRepository(Actor::class)->findAll();
        return $this->json($this->toApi($actors));
    }

    /**
     * @Route("/actor/{id}", name="actor_view", methods={"GET"})
     */
    public function view(Actor $actor)
    {
        $data = $this->toApi([$actor]);
        return $this->json($data[0]);
    }

    /**
     * @Route("/actor", name="actor_create", methods={"POST"})
     */
    public function create(Request $request)
    {
        dd($request->getContent());
    }

    /**
     * @Route("/actor/{id}", name="actor_update", methods={"PUT"})
     */
    public function update(Actor $actor, Request $request)
    {
        dd($request->getContent());
    }

    /**
     * @param Actor[] $actors
     * @return array
     */
    private function toApi($actors)
    {
        $params = [];

        foreach ($actors as $actor) {
            $params[] = [
                'id' => $actor->getId(),
                'name' => $actor->getName(),
                'sex' => $actor->getSex(),
                'age' => $actor->getAge(),
                'country' => $actor->getCountry()->getName(),
            ];
        }

        return $params;
    }
}