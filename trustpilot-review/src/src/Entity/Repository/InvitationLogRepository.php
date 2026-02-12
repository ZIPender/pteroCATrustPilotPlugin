<?php

namespace Plugins\TrustpilotReview\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Plugins\TrustpilotReview\Entity\InvitationLog;

class InvitationLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InvitationLog::class);
    }

    /**
     * @return InvitationLog[]
     */
    public function findPendingInvitations(): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.status = :status')
            ->andWhere('i.scheduledAt <= :now')
            ->setParameter('status', InvitationLog::STATUS_PENDING)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('i.scheduledAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByUserAndServer(int $userId, int $serverId): ?InvitationLog
    {
        return $this->createQueryBuilder('i')
            ->where('i.userId = :userId')
            ->andWhere('i.serverId = :serverId')
            ->setParameter('userId', $userId)
            ->setParameter('serverId', $serverId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function hasExistingInvitation(int $userId, int $serverId): bool
    {
        $result = $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.userId = :userId')
            ->andWhere('i.serverId = :serverId')
            ->setParameter('userId', $userId)
            ->setParameter('serverId', $serverId)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $result > 0;
    }

    public function countByStatus(string $status): int
    {
        return (int) $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->where('i.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return InvitationLog[]
     */
    public function findRecent(int $limit = 20): array
    {
        return $this->createQueryBuilder('i')
            ->orderBy('i.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
