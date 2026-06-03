<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260603091332 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE customer_order (id INT AUTO_INCREMENT NOT NULL, customer_first_name VARCHAR(100) NOT NULL, customer_lastname VARCHAR(100) NOT NULL, customer_email VARCHAR(180) NOT NULL, customer_phone VARCHAR(20) DEFAULT NULL, delivery_address VARCHAR(255) DEFAULT NULL, delivery�postal_code VARCHAR(20) DEFAULT NULL, delivery_city VARCHAR(100) DEFAULT NULL, delivery_time TIME NOT NULL, delivery_date DATE NOT NULL, delivery_place VARCHAR(255) DEFAULT NULL, person_count INT NOT NULL, menu_price NUMERIC(10, 2) DEFAULT NULL, delivery_price NUMERIC(10, 2) DEFAULT NULL, discount_amount NUMERIC(10, 2) DEFAULT NULL, total_price NUMERIC(10, 2) NOT NULL, status VARCHAR(50) NOT NULL, cancel_reason LONGTEXT DEFAULT NULL, contact_mode VARCHAR(50) DEFAULT NULL, create_at DATETIME NOT NULL, update_at DATETIME DEFAULT NULL, user_id INT DEFAULT NULL, menu_id INT DEFAULT NULL, INDEX IDX_3B1CE6A3A76ED395 (user_id), INDEX IDX_3B1CE6A3CCD7E912 (menu_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE dish (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, type VARCHAR(50) NOT NULL, create_at DATE NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE menu (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, theme VARCHAR(100) DEFAULT NULL, diet VARCHAR(100) DEFAULT NULL, minimum_persons INT NOT NULL, base_price NUMERIC(10, 2) NOT NULL, condition_text LONGTEXT DEFAULT NULL, stock_available INT DEFAULT NULL, create_at DATETIME NOT NULL, update_at DATE DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE menu_dish (menu_id INT NOT NULL, dish_id INT NOT NULL, INDEX IDX_5D327CF6CCD7E912 (menu_id), INDEX IDX_5D327CF6148EB0CB (dish_id), PRIMARY KEY (menu_id, dish_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE customer_order ADD CONSTRAINT FK_3B1CE6A3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE customer_order ADD CONSTRAINT FK_3B1CE6A3CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
        $this->addSql('ALTER TABLE menu_dish ADD CONSTRAINT FK_5D327CF6CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_dish ADD CONSTRAINT FK_5D327CF6148EB0CB FOREIGN KEY (dish_id) REFERENCES dish (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user CHANGE adress address VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer_order DROP FOREIGN KEY FK_3B1CE6A3A76ED395');
        $this->addSql('ALTER TABLE customer_order DROP FOREIGN KEY FK_3B1CE6A3CCD7E912');
        $this->addSql('ALTER TABLE menu_dish DROP FOREIGN KEY FK_5D327CF6CCD7E912');
        $this->addSql('ALTER TABLE menu_dish DROP FOREIGN KEY FK_5D327CF6148EB0CB');
        $this->addSql('DROP TABLE customer_order');
        $this->addSql('DROP TABLE dish');
        $this->addSql('DROP TABLE menu');
        $this->addSql('DROP TABLE menu_dish');
        $this->addSql('ALTER TABLE user CHANGE address adress VARCHAR(100) DEFAULT NULL');
    }
}
