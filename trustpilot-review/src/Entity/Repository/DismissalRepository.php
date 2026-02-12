<?php

namespace Plugins\TrustpilotReview\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Plugins\TrustpilotReview\Entity\Dismissal;

/**
 * Repository for Dismissal entity.
 *
 * Provides custom query methods for dismissal-related operations.
 */
class DismissalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dismissal::class);
    }

    /**
     * Check if a user has dismissed the popup for a specific server.
     */
    public function isDismissed(int $userId, int $serverId): bool
    {
        $qb = $this->createQueryBuilder('d');

        $result = $qb->select('COUNT(d.id)')
            ->where('d.userId = :userId')
            ->andWhere('d.serverId = :serverId')
            ->setParameter('userId', $userId)
            ->setParameter('serverId', $serverId)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $result > 0;
    }

    /**
     * Find dismissal by user and server.
     */
    public function findByUserAndServer(int $userId, int $serverId): ?Dismissal
    {
        return $this->createQueryBuilder('d')
            ->where('d.userId = :userId')
            ->andWhere('d.serverId = :serverId')
            ->setParameter('userId', $userId)
            ->setParameter('serverId', $serverId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get total count of dismissals.
     */
    public function countTotal(): int
    {
        return (int) $this->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Get count of unique users who dismissed.
     */
    public function countUniqueUsers(): int
    {
        return (int) $this->createQueryBuilder('d')
            ->select('COUNT(DISTINCT d.userId)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find all dismissals for a specific user.
     *
     * @return Dismissal[]
     */
    public function findByUser(int $userId): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.userId = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('d.dismissedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get recent dismissals.
     *
     * @return Dismissal[]
     */
    public function findRecent(int $limit = 10): array
    {
        return $this->createQueryBuilder('d')
            ->orderBy('d.dismissedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
