<?php


namespace App\Controller;

use App\Entity\Company;
use App\Repository\CompanyRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CompanyController extends AbstractController
{
    /**
     * @Route("/companies", name="companies_list", methods={"GET"})
     */
    public function list(Request $request)
    {
        /** @var CompanyRepository $repository */
        $repository = $this->getDoctrine()->getRepository(Company::class);
        $defaultLimit = $this->getParameter('default_item_limit');

        /** @var Company[] $companies */
        if ($request->get('search')) {
            $companies = $repository->findByName($request->get('search'), $request->get('limit', $defaultLimit), $request->get('offset'));
        } else {
            $companies = $repository->findForPage($request->get('limit', $defaultLimit), $request->get('offset'));
        }
        return $this->json($this->toApi($companies));
    }

    /**
     * @Route("/genre/{id}", name="company_view", methods={"GET"})
     */
    public function view(Company $company)
    {
        $data = $this->toApi([$company]);
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