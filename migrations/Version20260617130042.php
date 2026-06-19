<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260617130042 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_order CHANGE customer_phone customer_phone VARCHAR(20) NOT NULL, CHANGE delivery_address delivery_address VARCHAR(255) NOT NULL, CHANGE delivery_postal_code delivery_postal_code VARCHAR(20) NOT NULL, CHANGE delivery_city delivery_city VARCHAR(100) NOT NULL, CHANGE delivery_place delivery_place VARCHAR(255) NOT NULL, CHANGE customer_lastname customer_last_name VARCHAR(100) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_order CHANGE customer_phone customer_phone VARCHAR(20) DEFAULT NULL, CHANGE delivery_address delivery_address VARCHAR(255) DEFAULT NULL, CHANGE delivery_postal_code delivery_postal_code VARCHAR(20) DEFAULT NULL, CHANGE delivery_city delivery_city VARCHAR(100) DEFAULT NULL, CHANGE delivery_place delivery_place VARCHAR(255) DEFAULT NULL, CHANGE customer_last_name customer_lastname VARCHAR(100) NOT NULL');
    }
}
