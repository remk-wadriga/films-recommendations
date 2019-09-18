<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Country;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class CompanyFixtures extends AbstractFixture implements DependentFixtureInterface
{
    protected $isEnabled = false;

    public function loadData(ObjectManager $manager)
    {
        $this->createMany(Company::class, 350, function (Company $company, int $i) {
            /** @var Country $country */
            $country = $this->getRandomReference(Country::class);
            $company
                ->setCountry($country)
                ->setName($this->faker->name)
                ->setStaff($this->faker->numberBetween(20, 10000))
            ;
        });
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CountryFixtures::class,
        ];
    }
}
