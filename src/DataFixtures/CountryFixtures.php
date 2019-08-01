<?php

namespace App\DataFixtures;

use App\Entity\Country;
use Doctrine\Common\Persistence\ObjectManager;

class CountryFixtures extends AbstractFixture
{
    protected $isEnabled = false;

    public function loadData(ObjectManager $manager)
    {
        $requiredAttributes = ['name', 'code'];

        $this->loadFromFile(Country::class, 'countries.csv', $requiredAttributes);

        $manager->flush();
    }
}
