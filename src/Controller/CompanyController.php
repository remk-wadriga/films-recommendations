<?php


namespace App\Controller;

use App\Entity\Company;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CompanyController extends AbstractController
{
    /**
     * @Route("/companies", name="companies_list", methods={"GET"})
     */
    public function list()
    {
        /** @var Company[] $companies */
        $companies = $this->getDoctrine()->getRepository(Company::class)->findAll();
        return $this->json($this->toApi($companies));
    }

    /**
     * @Route("/genre/{id}", name="company_view", methods={"GET"})
     */
    public function view(Company $company)
    {
        $data = $this->toApi([$genre]);
        return $this->json($data[0]);
    }

    /**
     * @Route("/genre", name="company_create", methods={"POST"})
     */
    public function create(Request $request)
    {
        dd($request->getContent());
    }

    /**
     * @Route("/genre/{id}", name="company_update", methods={"PUT"})
     */
    public function update(Company $company, Request $request)
    {
        dd($request->getContent());
    }

    /**
     * @param Company[] $companies
     * @return array
     */
    private function toApi($companies)
    {
        $params = [];

        foreach ($companies as $company) {
            $params[] = [
                'id' => $company->getId(),
                'name' => $company->getName(),
                'country' => $company->getCountry()->getName(),
            ];
        }

        return $params;
    }
}