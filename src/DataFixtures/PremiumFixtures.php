<?php

namespace App\DataFixtures;

use App\Entity\Country;
use App\Entity\Premium;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class PremiumFixtures extends AbstractFixture implements DependentFixtureInterface
{
    protected $isEnabled = false;

    public function loadData(ObjectManager $manager)
    {
        $requiredAttributes = ['name', 'country'];
        /** @var Country[] $countries */
        $countries = $this->getRandomReferences(Country::class);

        $this->loadFromFile(Premium::class, 'premium.csv', $requiredAttributes, function (Premium $premium, array $data, $i) use ($countries) {
            $correctCountry = null;
            foreach ($countries as $country) {
                if ($country->getCode() == $data['country']) {
                    $correctCountry = $country;
                    break;
                }
            }
            if ($correctCountry === null) {
                throw new \Exception(sprintf('Can not find the country with code "%s"', $data['country']));
            }

            $premium
                ->setName($data['name'])
                ->setCountry($correctCountry)
            ;
            if ($this->faker->numberBetween(0, 1) === 0) {
                $prize = $this->faker->numberBetween(1000, 10000);
                $premium->setPrize($prize);
                if ($this->faker->numberBetween(0, 1) === 0) {
                    $prize += $this->faker->numberBetween(1000, 10000);
                    $premium->setPrize($prize);
                    if ($this->faker->numberBetween(0, 1) === 0) {
                        $prize += $this->faker->numberBetween(1000, 10000);
                        $premium->setPrize($prize);
                    }
                }
            }
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
