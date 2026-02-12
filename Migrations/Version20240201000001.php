<?php

declare(strict_types=1);

namespace Plugins\TrustpilotReview\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240201000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create plg_trustpilot_invitation_log table for tracking AFS review invitations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE plg_trustpilot_invitation_log (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT NOT NULL,
                user_email VARCHAR(255) NOT NULL,
                user_name VARCHAR(255) NOT NULL,
                server_id INT NOT NULL,
                reference_number VARCHAR(100) NOT NULL,
                status VARCHAR(20) NOT NULL DEFAULT \'pending\',
                scheduled_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                sent_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                error_message LONGTEXT DEFAULT NULL,
                trustpilot_response LONGTEXT DEFAULT NULL,
                created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                PRIMARY KEY(id),
                UNIQUE INDEX unique_user_server_invitation (user_id, server_id),
                INDEX idx_invitation_status_scheduled (status, scheduled_at)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS plg_trustpilot_invitation_log');
    }
}
