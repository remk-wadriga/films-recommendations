<?php


namespace App\Controller;

use App\Entity\Premium;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PremiumController extends AbstractController
{
    /**
     * @Route("/premiums", name="premiums_list", methods={"GET"})
     */
    public function list()
    {
        /** @var Premium[] $premiums */
        $premiums = $this->getDoctrine()->getRepository(Premium::class)->findAll();
        return $this->json($this->toApi($premiums));
    }

    /**
     * @Route("/premium/{id}", name="premium_view", methods={"GET"})
     */
    public function view(Premium $premium)
    {
        $data = $this->toApi([$premium]);
        return $this->json($data[0]);
    }

    /**
     * @Route("/premium", name="premium_create", methods={"POST"})
     */
    public function create(Request $request)
    {
        dd($request->getContent());
    }

    /**
     * @Route("/premium/{id}", name="genre_update", methods={"PUT"})
     */
    public function update(Premium $premium, Request $request)
    {
        dd($request->getContent());
    }

    /**
     * @param Premium[] $premiums
     * @return array
     */
    private function toApi($premiums)
    {
        $params = [];

        foreach ($premiums as $premium) {
            $params[] = [
                'id' => $premium->getId(),
                'name' => $premium->getName(),
                'prize' => $premium->getPrize(),
                'country' => $premium->getCountry()->getName(),
            ];
        }

        return $params;
    }
}