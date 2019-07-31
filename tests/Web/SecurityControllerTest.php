<?php

namespace App\Tests\Web;

use App\Tests\AbstractWebTestCase;

class SecurityControllerTest extends AbstractWebTestCase
{
    public function testSomething()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Hello World');
    }
}
