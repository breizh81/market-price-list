<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    public function testHomepageLoadsSuccessfully()
    {
        $client = static::createClient();

        $client->request('GET', '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Check if the homepage contains a specific text (example)
        $this->assertStringContainsString('Welcome to the Home Page', $client->getResponse()->getContent());
    }
}
