<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260611110043 extends AbstractMigration
{
     public function isTransactional(): bool
    {
        return false;
    }

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_order CHANGE delivery�postal_code delivery_postal_code VARCHAR(20) DEFAULT NULL, CHANGE create_at created_at DATETIME NOT NULL, CHANGE update_at updated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_order CHANGE delivery_postal_code delivery�postal_code VARCHAR(20) DEFAULT NULL, CHANGE created_at create_at DATETIME NOT NULL, CHANGE updated_at update_at DATETIME DEFAULT NULL');
    }
}
