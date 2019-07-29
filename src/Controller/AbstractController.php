<?php


namespace App\Controller;

use Mcfedr\JsonFormBundle\Controller\JsonController;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractController extends JsonController
{
    public function getRequestFilters(Request $request): array
    {
        return [];
    }
}