<?php


namespace App\Service;

use App\Entity\Film;
use App\Entity\User;

class FilmService extends AbstractService
{
    /**
     * @param User $user
     * @return Film[]
     */
    public function getRecommendedForUser(User $user)
    {
        $repository = $this->em->getRepository(Film::class);
        return $repository->findAll();
    }
}