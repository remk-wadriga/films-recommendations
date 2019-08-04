<?php


namespace App\Controller;

use App\Entity\User;
use Mcfedr\JsonFormBundle\Controller\JsonController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AbstractController
 * @package App\Controller
 *
 * @method User getUser
 * @property ServiceLocator $container
 */
abstract class AbstractController extends JsonController
{
    public function getRequestFilters(Request $request): array
    {
        return [];
    }
}