<?php

namespace App\DataFixtures;

use App\Entity\Company;
use App\Entity\Producer;
use App\Entity\Types\Enum\GenderEnum;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class ProducerFixtures extends AbstractFixture implements DependentFixtureInterface
{
    protected $isEnabled = false;

    public function loadData(ObjectManager $manager)
    {
        $this->createMany(Producer::class, 500, function (Producer $producer, int $i) {
            /** @var Company $company */
            $company = $this->getRandomReference(Company::class);
            $producer
                ->setCompany($company)
                ->setName($this->faker->firstName . ' ' . $this->faker->lastName)
                ->setSex($this->faker->randomElement(GenderEnum::getAvailableTypes()))
                ->setAge($this->faker->numberBetween(24, 90))
            ;
        });
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            CompanyFixtures::class,
        ];
    }
}
