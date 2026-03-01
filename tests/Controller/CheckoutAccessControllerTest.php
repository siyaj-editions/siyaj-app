<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CheckoutAccessControllerTest extends WebTestCase
{
    public function testCheckoutInformationRedirectsToLoginWhenAnonymous(): void
    {
        $client = static::createClient();
        $client->request('GET', '/checkout/informations');

        self::assertResponseRedirects('http://localhost/login', 302);
    }

    public function testCheckoutStartRedirectsToLoginWhenAnonymous(): void
    {
        $client = static::createClient();
        $client->request('GET', '/checkout/start?session_id=test_session');

        self::assertResponseRedirects('http://localhost/login', 302);
    }

    public function testCheckoutDebugRedirectsToLoginWhenAnonymous(): void
    {
        $client = static::createClient();
        $client->request('GET', '/checkout/debug?session_id=test_session');

        self::assertResponseRedirects('http://localhost/login', 302);
    }
}
