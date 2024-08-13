<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240812122608 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add state column to product table';
    }

    public function up(Schema $schema): void
    {
        // Add state column
        $this->addSql('ALTER TABLE product ADD state VARCHAR(10) NOT NULL, ADD marking VARCHAR(255) NULL');
    }

    public function down(Schema $schema): void
    {
        // Remove state column
        $this->addSql('ALTER TABLE product DROP COLUMN state');
    }
}
