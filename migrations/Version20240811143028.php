<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240811143028 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial migration to create tables and sequences';
    }

    public function up(Schema $schema): void
    {
        // Create sequences
        $this->addSql('CREATE SEQUENCE import_batch_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE product_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE supplier_id_seq INCREMENT BY 1 MINVALUE 1 START 1');

        // Create tables
        $this->addSql('CREATE TABLE import_batch (
            id INT NOT NULL DEFAULT nextval(\'import_batch_id_seq\'),
            is_completed BOOLEAN NOT NULL,
            total_messages INT NOT NULL,
            processed_messages INT NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE product (
            id INT NOT NULL DEFAULT nextval(\'product_id_seq\'),
            supplier_id INT NOT NULL,
            code VARCHAR(6) NOT NULL,
            description TEXT NOT NULL,
            price DOUBLE PRECISION NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE TABLE supplier (
            id INT NOT NULL DEFAULT nextval(\'supplier_id_seq\'),
            name VARCHAR(255) NOT NULL,
            PRIMARY KEY(id)
        )');

        // Create indexes and foreign key constraints
        $this->addSql('CREATE INDEX IDX_D34A04AD2ADD6D8C ON product (supplier_id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD2ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // Drop constraints and tables
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04AD2ADD6D8C');
        $this->addSql('DROP TABLE import_batch');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE supplier');

        // Drop sequences
        $this->addSql('DROP SEQUENCE import_batch_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE product_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE supplier_id_seq CASCADE');
    }
}
