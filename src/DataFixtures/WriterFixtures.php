<?php

namespace App\DataFixtures;

use App\Entity\Writer;
use App\Entity\Country;
use App\Entity\Types\Enum\GenderEnum;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class WriterFixtures extends AbstractFixture implements DependentFixtureInterface
{
    protected $isEnabled = false;

    public function loadData(ObjectManager $manager)
    {
        $this->createMany(Writer::class, 1500, function (Writer $writer, int $i) {
            /** @var Country $country */
            $country = $this->getRandomReference(Country::class);
            $writer
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
