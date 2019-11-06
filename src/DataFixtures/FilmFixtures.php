<?php

namespace App\DataFixtures;

use App\Entity\Actor;
use App\Entity\Company;
use App\Entity\Director;
use App\Entity\Film;
use App\Entity\Genre;
use App\Entity\Language;
use App\Entity\Premium;
use App\Entity\Producer;
use App\Entity\User;
use App\Entity\Writer;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class FilmFixtures extends AbstractFixture implements DependentFixtureInterface
{
    protected $isEnabled = false;

    public function loadData(ObjectManager $manager)
    {
        $this->createMany(Film::class, 10000, function (Film $film, int $i) {
            /** @var User $user */
            /** @var Genre[] $genres */
            /** @var Company[] $companies */
            /** @var Director[] $directors */
            /** @var Actor[] $actors */
            /** @var Producer[] $producers */
            /** @var Writer[] $writers */
            /** @var Premium[] $premiums */
            /** @var Language[] $languages */
            $user = $this->getRandomReference(User::class);
            $genres = $this->getRandomReferences(Genre::class, $this->faker->numberBetween(1, 3));
            $companies = $this->getRandomReferences(Company::class, $this->faker->numberBetween(1, 5));
            $directorsCount = $this->faker->numberBetween(1, 5) === 1 ? $this->faker->numberBetween(1, 3) : 1;
            $directors = $this->getRandomReferences(Director::class, $directorsCount);
            $actors = $this->getRandomReferences(Actor::class, $this->faker->numberBetween(1, 15));
            $producersCount = $this->faker->numberBetween(1, 5) === 1 ? $this->faker->numberBetween(1, 3) : 1;
            $producers = $this->getRandomReferences(Producer::class, $producersCount);
            $writersCount = $this->faker->numberBetween(1, 5) === 1 ? $this->faker->numberBetween(1, 3) : 1;
            $writers = $this->getRandomReferences(Writer::class, $writersCount);
            $premiumsCount = $this->faker->numberBetween(1, 20) === 1 ? $this->faker->numberBetween(0, 3) : 0;
            $premiums = $this->getRandomReferences(Premium::class, $premiumsCount);
            $languages = $this->getRandomReferences(Language::class, $this->faker->numberBetween(1, 4));

            foreach ($genres as $genre) {
                $film->addGenre($genre);
            }
            foreach ($companies as $company) {
                $film->addCompany($company);
            }
            foreach ($directors as $director) {
                $film->addDirector($director);
            }
            foreach ($actors as $actor) {
                $film->addActor($actor);
            }
            foreach ($producers as $producer) {
                $film->addProducer($producer);
            }
            foreach ($writers as $writer) {
                $film->addWriter($writer);
            }
            foreach ($premiums as $premium) {
                $film->addPremium($premium);
            }
            foreach ($languages as $language) {
                $film->addLanguage($language);
            }

            $name = $this->faker->name;
            if ($this->faker->numberBetween(1, 2) === 1) {
                $name .= ' ' . $this->faker->name;
                if ($this->faker->numberBetween(1, 2) === 1) {
                    $name .= ' ' . $this->faker->name;
                    if ($this->faker->numberBetween(1, 2) === 1) {
                        $name .= ' ' . $this->faker->name;
                    }
                }
            }

            $budget = $this->faker->numberBetween(1000, 500000000);
            $sales = $budget;
            if ($this->faker->numberBetween(1, 3) === 1) {
                if ($this->faker->numberBetween(1, 3) === 1) {
                    $sales *= $this->faker->numberBetween(1, 15);
                } else {
                    $sales += $this->faker->numberBetween(1, 100000000);
                }
            } else {
                if ($this->faker->numberBetween(1, 3) === 1) {
                    $sales /= $this->faker->numberBetween(1, 15);
                } else {
                    $sales -= $this->faker->numberBetween(1, $sales);
                }
            }

            $date = null;
            if ($this->faker->numberBetween(1, 10) === 1) {
                $date = $this->faker->dateTimeBetween('-20 years');
            }
            if ($date === null && $this->faker->numberBetween(1, 15) === 1) {
                $date = $this->faker->dateTimeBetween('-40 years');
            }
            if ($date === null && $this->faker->numberBetween(1, 20) === 1) {
                $date = $this->faker->dateTimeBetween('-60 years');
            }
            if ($date === null && $this->faker->numberBetween(1, 25) === 1) {
                $date = $this->faker->dateTimeBetween('-80 years');
            }
            if ($date === null && $this->faker->numberBetween(1, 30) === 1) {
                $date = $this->faker->dateTimeBetween('-100 years');
            }
            if ($date === null && $this->faker->numberBetween(1, 40) === 1) {
                $date = $this->faker->dateTimeBetween('-120 years');
            }
            if ($date === null) {
                $date = $this->faker->dateTimeBetween('-10 years');
            }

            $duration = 0;
            if ($this->faker->numberBetween(1, 2) === 1) {
                $duration = $this->faker->numberBetween(75, 120);
            }
            if ($duration === 0 && $this->faker->numberBetween(1, 3) === 1) {
                $duration = $this->faker->numberBetween(60, 180);
            }
            if ($duration === 0 && $this->faker->numberBetween(1, 4) === 1) {
                $duration = $this->faker->numberBetween(180, 1000);
            }
            if ($duration === 0) {
                $duration = $this->faker->numberBetween(75, 120);
            }

            $film
                ->setUser($user)
                ->setName($name)
                ->setDescription($this->faker->text)
                ->setPoster('/img/poster/default_poster.jpg')
                ->setBudget($budget)
                ->setSales((int)$sales)
                ->setDate($date)
                ->setDuration($duration)
                ->setSlogan($this->faker->text(230))
                ->setRating($this->faker->randomFloat(1, 0, 10))
            ;
        });

        $manager->flush();
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
            LanguageFixtures::class,
        ];
    }
}
