<?php

namespace App\Tests\Entity;

use App\Entity\Event;
use App\Entity\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public static function remainingPlacesProvider(): array
    {
        return [
            'aucun inscrit' => [10, 0, 10],
            'quelques inscrits' => [10, 3, 7],
            'complet' => [2, 2, 0],
        ];
    }

    #[DataProvider('remainingPlacesProvider')]
    public function testGetRemainingPlaces(int $places, int $nbParticipants, int $expected): void
    {
        $event = new Event();
        $event->setPlaces($places);

        for ($i = 0; $i < $nbParticipants; $i++) {
            $event->addParticipant(new User());
        }

        $this->assertSame($expected, $event->getRemainingPlaces());
    }

    public static function categoryLabelProvider(): array
    {
        return [
            ['concert', 'Concert'],
            ['conference', 'Conférence'],
            ['atelier', 'Atelier'],
            ['sport', 'Sport'],
        ];
    }

    #[DataProvider('categoryLabelProvider')]
    public function testGetCategoryLabel(string $category, string $expected): void
    {
        $event = new Event();
        $event->setCategory($category);

        $this->assertSame($expected, $event->getCategoryLabel());
    }
}