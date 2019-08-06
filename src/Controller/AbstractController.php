<?php


namespace App\Controller;

use App\Entity\User;
use App\Helpers\ListedEntityInterface;
use Mcfedr\JsonFormBundle\Controller\JsonController;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
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

    public function formatDate(?\DateTimeInterface $date, string $format = null): string
    {
        try {
            $format = $this->getParameter('frontend_date_format');
        } catch (ServiceNotFoundException $e) {
            $format = null;
        }
        if ($date === null || empty($format)) {
            return '';
        }
        return $date->format($format);
    }

    /**
     * @param ListedEntityInterface[] $items
     * @return array
     */
    protected function getItemsList($items)
    {
        $list = [];
        foreach ($items as $item) {
            $list[] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
            ];
        }
        return $list;
    }
}