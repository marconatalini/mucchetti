<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260718083818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE stamping (id INT AUTO_INCREMENT NOT NULL, employee_id INT NOT NULL, missed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(50) NOT NULL, stamping_type VARCHAR(30) NOT NULL, evaluated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', declared_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_F94130C08C03F15C (employee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE stamping ADD CONSTRAINT FK_F94130C08C03F15C FOREIGN KEY (employee_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE permit ADD postdated TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE stamping DROP FOREIGN KEY FK_F94130C08C03F15C');
        $this->addSql('DROP TABLE stamping');
        $this->addSql('ALTER TABLE permit DROP postdated');
    }
}
