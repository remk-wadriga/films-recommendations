<?php

namespace App\DataFixtures;

use App\Entity\Language;
use Doctrine\Common\Persistence\ObjectManager;

class LanguageFixtures extends AbstractFixture
{
    protected $isEnabled = false;

    public function loadData(ObjectManager $manager)
    {
        $requiredAttributes = ['name', 'code'];

        $this->loadFromFile(Language::class, 'languages.csv', $requiredAttributes);

        $manager->flush();
    }
}
