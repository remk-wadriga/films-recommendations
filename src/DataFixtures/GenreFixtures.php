<?php

namespace App\DataFixtures;

use App\Entity\Genre;
use Doctrine\Common\Persistence\ObjectManager;

class GenreFixtures extends AbstractFixture
{
    protected $isEnabled = false;

    public function loadData(ObjectManager $manager)
    {
        $requiredAttributes = ['name'];

        $this->loadFromFile(Genre::class, 'genres.csv', $requiredAttributes);

        $manager->flush();
    }
}
