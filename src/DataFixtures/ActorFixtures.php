<?php

namespace App\DataFixtures;

use App\Entity\Actor;
use App\Entity\Country;
use App\Entity\Types\Enum\GenderEnum;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ActorFixtures extends AbstractFixture implements DependentFixtureInterface
{
    protected $isEnabled = false;

    public function loadData(ObjectManager $manager)
    {
        $this->createMany(Actor::class, 4500, function (Actor $actor, int $i) {
            /** @var Country $country */
            $country = $this->getRandomReference(Country::class);
            $actor
                ->setCountry($country)
                ->setName($this->faker->firstName . ' ' . $this->faker->lastName)
                ->setSex($this->faker->randomElement(GenderEnum::getAvailableTypes()))
                ->setAge($this->faker->numberBetween(7, 90))
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
