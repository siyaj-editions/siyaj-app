<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContactControllerTest extends WebTestCase
{
    public function testContactPageLoads(): void
    {
        $client = static::createClient();
        $client->request('GET', '/contact');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Nous écrire');
    }

    public function testContactFormDisplaysValidationErrorsWhenEmpty(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        $form = $crawler->selectButton('Envoyer mon message')->form([
            'contact_form[firstname]' => '',
            'contact_form[lastname]' => '',
            'contact_form[email]' => '',
            'contact_form[subject]' => '',
            'contact_form[message]' => '',
            'contact_form[company]' => '',
        ]);

        $client->submit($form);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'Le prénom est requis.');
        self::assertSelectorTextContains('body', 'Le nom est requis.');
        self::assertSelectorTextContains('body', 'Le motif est requis.');
        self::assertSelectorTextContains('body', 'Le message est requis.');
    }

    public function testContactFormRedirectsToSuccessWhenHoneypotIsFilled(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        $form = $crawler->selectButton('Envoyer mon message')->form([
            'contact_form[firstname]' => 'Ludwig',
            'contact_form[lastname]' => 'Elatre',
            'contact_form[email]' => 'user@mail.com',
            'contact_form[subject]' => 'communication',
            'contact_form[message]' => 'Bonjour, ceci est un message suffisamment long.',
            'contact_form[company]' => 'https://spam.example',
        ]);

        $client->submit($form);

        self::assertResponseRedirects('/contact');
    }
}
