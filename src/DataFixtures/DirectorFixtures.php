<?php

namespace App\DataFixtures;

use App\Entity\Director;
use App\Entity\Country;
use App\Entity\Types\Enum\GenderEnum;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class DirectorFixtures extends AbstractFixture implements DependentFixtureInterface
{
    protected $isEnabled = false;

    public function loadData(ObjectManager $manager)
    {
        $this->createMany(Director::class, 1500, function (Director $director, int $i) {
            /** @var Country $country */
            $country = $this->getRandomReference(Country::class);
            $director
                ->setCountry($country)
                ->setName($this->faker->firstName . ' ' . $this->faker->lastName)
                ->setSex($this->faker->randomElement(GenderEnum::getAvailableTypes()))
                ->setAge($this->faker->numberBetween(20, 90))
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
