<?php

namespace Plugins\TrustpilotReview\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Plugins\TrustpilotReview\Entity\Repository\DismissalRepository;

/**
 * Dismissal entity for tracking when users dismiss the Trustpilot review popup.
 *
 * This entity tracks dismissals per user/server combination to ensure
 * users aren't repeatedly prompted after dismissing the popup.
 *
 * Table naming convention: plg_{shortname}_{table}
 */
#[ORM\Entity(repositoryClass: DismissalRepository::class)]
#[ORM\Table(name: 'plg_trustpilot_dismissal')]
#[ORM\Index(columns: ['user_id', 'server_id'], name: 'idx_trustpilot_user_server')]
#[ORM\UniqueConstraint(name: 'unique_user_server', columns: ['user_id', 'server_id'])]
class Dismissal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $userId;

    #[ORM\Column(type: Types::INTEGER)]
    private int $serverId;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $dismissedAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct()
    {
        $this->dismissedAt = new \DateTimeImmutable();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;

        return $this;
    }

    public function getServerId(): int
    {
        return $this->serverId;
    }

    public function setServerId(int $serverId): self
    {
        $this->serverId = $serverId;

        return $this;
    }

    public function getDismissedAt(): \DateTimeImmutable
    {
        return $this->dismissedAt;
    }

    public function setDismissedAt(\DateTimeImmutable $dismissedAt): self
    {
        $this->dismissedAt = $dismissedAt;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
