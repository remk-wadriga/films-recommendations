<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Types\Enum\GenderEnum;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends AbstractFixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    protected function loadData(ObjectManager $manager)
    {
        // Make 1500 users by 15 packets by 100 users
        for ($y = 0; $y < 15; $y++) {
            $this->createMany(User::class, 100, function (User $user, int $i) use ($y) {
                if ($y === 0 && $i === 0) {
                    // Create default user
                    $user
                        ->setEmail('user@gmail.com')
                        ->setFirstName('Default')
                        ->setLastName('User')
                        ->setAge($this->faker->numberBetween(15, 100))
                        ->setSex($this->faker->randomElement(GenderEnum::getAvailableTypes()))
                        ->setAboutMe($this->faker->text)
                        ->setPassword($this->encoder->encodePassword($user, 'test'))
                        ->setRoles(['ROLE_USER']);
                    return;
                }

                // Create random users
                $user
                    ->setEmail(($y + 1) . $i . $this->faker->email)
                    ->setFirstName($this->faker->firstName)
                    ->setLastName($this->faker->lastName)
                    ->setAge($this->faker->numberBetween(7, 120))
                    ->setSex($this->faker->randomElement(GenderEnum::getAvailableTypes()))
                    ->setAboutMe($this->faker->text)
                    ->setPassword($this->encoder->encodePassword($user, $user->getEmail()))
                    ->setRoles(['ROLE_USER']);
            }, $y);

            $manager->flush();
        }
    }
}
