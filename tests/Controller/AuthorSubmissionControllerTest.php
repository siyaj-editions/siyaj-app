<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthorSubmissionControllerTest extends WebTestCase
{
    public function testManuscriptFormRedirectsToSuccessWhenHoneypotIsFilled(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/auteurs/soumettre-manuscrit');

        $form = $crawler->selectButton('Envoyer le manuscrit')->form([
            'manuscript_submission[firstname]' => 'Ludwig',
            'manuscript_submission[lastname]' => 'Elatre',
            'manuscript_submission[email]' => 'user@example.com',
            'manuscript_submission[phone]' => '',
            'manuscript_submission[bookTitle]' => 'Mon manuscrit',
            'manuscript_submission[genre]' => 'Roman',
            'manuscript_submission[synopsis]' => 'Synopsis suffisamment long pour valider le formulaire.',
            'manuscript_submission[company]' => 'bot-value',
        ]);
        $tempFile = tempnam(sys_get_temp_dir(), 'manuscript');
        file_put_contents($tempFile, '%PDF-1.4 fake pdf');
        $pdfPath = $tempFile . '.pdf';
        rename($tempFile, $pdfPath);
        $form['manuscript_submission[manuscriptFile]']->upload($pdfPath);

        $client->submit($form);

        self::assertResponseRedirects('/auteurs/soumettre-manuscrit');
    }
}
