<?php

declare(strict_types=1);

namespace Plugins\TrustpilotReview\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Create dismissal table for Trustpilot Review plugin.
 *
 * This table tracks when users dismiss the review popup
 * to prevent showing it repeatedly.
 */
final class Version20240101000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create plg_trustpilot_dismissal table for tracking review popup dismissals';
    }

    public function up(Schema $schema): void
    {
        // Create dismissal table (MySQL compatible)
        // The unique constraint on (user_id, server_id) automatically creates an index
        $this->addSql('
            CREATE TABLE plg_trustpilot_dismissal (
                id INT AUTO_INCREMENT NOT NULL,
                user_id INT NOT NULL,
                server_id INT NOT NULL,
                dismissed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                PRIMARY KEY(id),
                UNIQUE INDEX unique_user_server (user_id, server_id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema): void
    {
        // Drop dismissal table
        $this->addSql('DROP TABLE IF EXISTS plg_trustpilot_dismissal');
    }
}
