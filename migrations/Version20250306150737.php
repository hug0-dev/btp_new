<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250306150737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE affectation (id INT AUTO_INCREMENT NOT NULL, equipe_id INT NOT NULL, chantier_id INT NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, nom VARCHAR(50) DEFAULT NULL, INDEX IDX_F4DD61D36D861B89 (equipe_id), INDEX IDX_F4DD61D3D0C0049D (chantier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chantier (id INT AUTO_INCREMENT NOT NULL, chef_chantier_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, chantier_prerequis JSON DEFAULT NULL, effectif_requis INT NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, image VARCHAR(255) DEFAULT NULL, INDEX IDX_636F27F622456F8F (chef_chantier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE equipe (id INT AUTO_INCREMENT NOT NULL, chef_equipe_id INT DEFAULT NULL, nom_equipe VARCHAR(255) NOT NULL, competance_equipe JSON NOT NULL, nombre INT NOT NULL, INDEX IDX_2449BA15BEF74F87 (chef_equipe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ouvrier (id INT AUTO_INCREMENT NOT NULL, equipe_id INT DEFAULT NULL, nom_ouvrier VARCHAR(100) NOT NULL, competences JSON NOT NULL, role VARCHAR(50) NOT NULL, INDEX IDX_ED5E7D256D861B89 (equipe_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE affectation ADD CONSTRAINT FK_F4DD61D36D861B89 FOREIGN KEY (equipe_id) REFERENCES equipe (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE affectation ADD CONSTRAINT FK_F4DD61D3D0C0049D FOREIGN KEY (chantier_id) REFERENCES chantier (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chantier ADD CONSTRAINT FK_636F27F622456F8F FOREIGN KEY (chef_chantier_id) REFERENCES ouvrier (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE equipe ADD CONSTRAINT FK_2449BA15BEF74F87 FOREIGN KEY (chef_equipe_id) REFERENCES ouvrier (id)');
        $this->addSql('ALTER TABLE ouvrier ADD CONSTRAINT FK_ED5E7D256D861B89 FOREIGN KEY (equipe_id) REFERENCES equipe (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE affectation DROP FOREIGN KEY FK_F4DD61D36D861B89');
        $this->addSql('ALTER TABLE affectation DROP FOREIGN KEY FK_F4DD61D3D0C0049D');
        $this->addSql('ALTER TABLE chantier DROP FOREIGN KEY FK_636F27F622456F8F');
        $this->addSql('ALTER TABLE equipe DROP FOREIGN KEY FK_2449BA15BEF74F87');
        $this->addSql('ALTER TABLE ouvrier DROP FOREIGN KEY FK_ED5E7D256D861B89');
        $this->addSql('DROP TABLE affectation');
        $this->addSql('DROP TABLE chantier');
        $this->addSql('DROP TABLE equipe');
        $this->addSql('DROP TABLE ouvrier');
        $this->addSql('DROP TABLE user');
    }
}
