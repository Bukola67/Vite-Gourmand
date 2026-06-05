<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260605104045 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }

    public function getDescription(): string
    {
        return 'Rename dish.create_at to created_at and change type from DATE to DATETIME';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dish CHANGE create_at created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE dish CHANGE created_at create_at DATE NOT NULL');
    }
}
