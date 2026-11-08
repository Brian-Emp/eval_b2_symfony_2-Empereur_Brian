<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * Événements validés et à venir, triés par date.
     * Si une catégorie est donnée, on ne garde que celle-ci.
     *
     * @return Event[]
     */
    public function findUpcoming(?string $category): array
    {
        $qb = $this->createQueryBuilder('e')
            ->andWhere('e.validated = true')
            ->andWhere('e.date > :now')
            ->setParameter('now', new \DateTime())
            ->orderBy('e.date', 'ASC');

        if ($category) {
            $qb->andWhere('e.category = :category')
                ->setParameter('category', $category);
        }

        return $qb->getQuery()->getResult();
    }
}
