<?php

namespace App\Tests\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;

class EventControllerTest extends WebTestCase
{
    public function testHome(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertCount(3, $crawler->filter('.event-card'));
        $this->assertSelectorTextNotContains('.event-list', 'Concert passé');
        $this->assertSelectorTextNotContains('.event-list', 'Tournoi de basket');
    }

    public function testShowFromHome(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $client->clickLink('Soirée jazz');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Soirée jazz');
    }

    public function testShowNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/evenement/9999');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCategoryFilter(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/?categorie=sport');

        $this->assertResponseIsSuccessful();
        $this->assertCount(1, $crawler->filter('.event-card'));
        $this->assertSelectorTextContains('.legend a.active', 'Sport');
    }

    public function testNewEventFormErrors(): void
    {
        $client = static::createClient();
        $eventRepository = static::getContainer()->get(EventRepository::class);
        $countBefore = $eventRepository->count([]);

        $client->request('GET', '/evenement/ajouter');
        $client->submitForm('Enregistrer l\'événement', [
            'event[title]' => '',
            'event[date]' => (new \DateTime('+5 days'))->format('Y-m-d\TH:i'),
            'event[information]' => 'Un événement de test',
            'event[category]' => 'concert',
            'event[places]' => 0,
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorTextContains('body', 'Le titre est obligatoire.');
        $this->assertSelectorTextContains('body', 'Le nombre de places doit être supérieur à 0.');

        $this->assertSame($countBefore, $eventRepository->count([]));
    }

    //bonus
    public function testMyEventsVisitorRedirected(): void
    {
        $client = static::createClient();
        $client->request('GET', '/mes-inscriptions');

        $this->assertResponseRedirects('/login');
    }

    public function testValidateVisitorRedirected(): void
    {
        $client = static::createClient();
        $client->request('GET', '/validate');

        $this->assertResponseRedirects('/login');
    }

    public function testMenuAdmin(): void
    {
        $client = static::createClient();
        $admin = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'admin@test.fr']);
        $client->loginUser($admin);
        $client->request('GET', '/');

        $this->assertSelectorTextContains('.nav', 'Validation');
    }

    public function testMenuUser(): void
    {
        $client = static::createClient();
        $user = static::getContainer()->get(UserRepository::class)->findOneBy(['email' => 'user@test.fr']);
        $client->loginUser($user);
        $client->request('GET', '/');

        $this->assertSelectorTextContains('.nav', 'Mes inscriptions');
        $this->assertSelectorTextNotContains('.nav', 'Validation');
    }

}