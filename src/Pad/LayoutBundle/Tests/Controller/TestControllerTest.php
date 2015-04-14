<?php

namespace Pad\LayoutBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TestControllerTest extends WebTestCase
{
    public function testTop()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/Top');
    }

}
