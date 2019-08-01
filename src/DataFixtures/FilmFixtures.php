<?php

namespace App\DataFixtures;

use App\Entity\Actor;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class FilmFixtures extends AbstractFixture implements DependentFixtureInterface
{
    protected $isEnabled = true;

    public function loadData(ObjectManager $manager)
    {
        $actor = $this->getRandomReference(Actor::class);
        dd($actor);

        //dd('OK');

        //$manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            CompanyFixtures::class,
            GenreFixtures::class,
            ActorFixtures::class,
            DirectorFixtures::class,
            ProducerFixtures::class,
            WriterFixtures::class,
            PremiumFixtures::class,
        ];
    }
}
