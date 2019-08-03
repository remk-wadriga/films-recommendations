<?php


namespace App\Controller;

use App\Entity\Writer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class WriterController extends AbstractController
{
    /**
     * @Route("/writers", name="writers_list", methods={"GET"})
     */
    public function list()
    {
        /** @var Writer[] $writers */
        $writers = $this->getDoctrine()->getRepository(Writer::class)->findAll();
        return $this->json($this->toApi($writers));
    }

    /**
     * @Route("/writer/{id}", name="writer_view", methods={"GET"})
     */
    public function view(Writer $writer)
    {
        $data = $this->toApi([$writer]);
        return $this->json($data[0]);
    }

    /**
     * @Route("/writer", name="writer_create", methods={"POST"})
     */
    public function create(Request $request)
    {
        dd($request->getContent());
    }

    /**
     * @Route("/writer/{id}", name="writer_update", methods={"PUT"})
     */
    public function update(Writer $writer, Request $request)
    {
        dd($request->getContent());
    }

    /**
     * @param Writer[] $writers
     * @return array
     */
    private function toApi($writers)
    {
        $params = [];

        foreach ($writers as $writer) {
            $params[] = [
                'id' => $writer->getId(),
                'name' => $writer->getName(),
                'sex' => $writer->getSex(),
                'age' => $writer->getAge(),
                'country' => $writer->getCountry()->getName(),
            ];
        }

        return $params;
    }
}