<?php


namespace App\Controller;

use App\Entity\Producer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProducerController extends AbstractController
{
    /**
     * @Route("/producers", name="producers_list", methods={"GET"})
     */
    public function list()
    {
        /** @var Producer[] $producers */
        $producers = $this->getDoctrine()->getRepository(Producer::class)->findAll();
        return $this->json($this->toApi($producers));
    }

    /**
     * @Route("/producer/{id}", name="producer_view", methods={"GET"})
     */
    public function view(Producer $producer)
    {
        $data = $this->toApi([$producer]);
        return $this->json($data[0]);
    }

    /**
     * @Route("/producer", name="producer_create", methods={"POST"})
     */
    public function create(Request $request)
    {
        dd($request->getContent());
    }

    /**
     * @Route("/producer/{id}", name="producer_update", methods={"PUT"})
     */
    public function update(Producer $producer, Request $request)
    {
        dd($request->getContent());
    }

    /**
     * @param Producer[] $producers
     * @return array
     */
    private function toApi($producers)
    {
        $params = [];

        foreach ($producers as $producer) {
            $params[] = [
                'id' => $producer->getId(),
                'name' => $producer->getName(),
                'sex' => $producer->getSex(),
                'age' => $producer->getAge(),
                'company' => $producer->getCompany()->getName(),
            ];
        }

        return $params;
    }
}