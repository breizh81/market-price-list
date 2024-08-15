<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class HomeControllerTest extends WebTestCase
{
    public function testHomepageLoadsSuccessfully()
    {
        $client = static::createClient();

        $client->request(Request::METHOD_GET, '/');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $this->assertStringContainsString('Welcome to the Home Page', $client->getResponse()->getContent());
    }
}
