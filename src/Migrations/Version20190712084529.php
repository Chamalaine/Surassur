<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190712084529 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE assure (id INT AUTO_INCREMENT NOT NULL, intermediaire_id INT NOT NULL, nom VARCHAR(50) NOT NULL, prenom VARCHAR(50) NOT NULL, date_naissance DATE NOT NULL, telephone VARCHAR(30) DEFAULT NULL, numero VARCHAR(9) NOT NULL, libelle VARCHAR(60) NOT NULL, ville VARCHAR(50) NOT NULL, cp VARCHAR(30) NOT NULL, complement VARCHAR(50) DEFAULT NULL, INDEX IDX_C779CC29F9D711AE (intermediaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE beneficiaire (id INT AUTO_INCREMENT NOT NULL, assure_id INT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, date_naissance DATE NOT NULL, relation VARCHAR(50) NOT NULL, INDEX IDX_B140D8021F4BE942 (assure_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE intermediaire (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(90) NOT NULL, password VARCHAR(900) NOT NULL, nom VARCHAR(60) NOT NULL, siret VARCHAR(30) DEFAULT NULL, date_creation DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE intermediaire_souscripteur (intermediaire_id INT NOT NULL, souscripteur_id INT NOT NULL, INDEX IDX_BB664C98F9D711AE (intermediaire_id), INDEX IDX_BB664C98A0B466D6 (souscripteur_id), PRIMARY KEY(intermediaire_id, souscripteur_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE listing (id INT AUTO_INCREMENT NOT NULL, souscripteur_id INT NOT NULL, intermediaire_id INT NOT NULL, nom VARCHAR(255) NOT NULL, date_creation DATETIME NOT NULL, date_envoi DATETIME DEFAULT NULL, INDEX IDX_CB0048D4A0B466D6 (souscripteur_id), INDEX IDX_CB0048D4F9D711AE (intermediaire_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE listing_assure (listing_id INT NOT NULL, assure_id INT NOT NULL, INDEX IDX_2381EDFAD4619D1A (listing_id), INDEX IDX_2381EDFA1F4BE942 (assure_id), PRIMARY KEY(listing_id, assure_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE souscripteur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(60) NOT NULL, email VARCHAR(60) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE assure ADD CONSTRAINT FK_C779CC29F9D711AE FOREIGN KEY (intermediaire_id) REFERENCES intermediaire (id)');
        $this->addSql('ALTER TABLE beneficiaire ADD CONSTRAINT FK_B140D8021F4BE942 FOREIGN KEY (assure_id) REFERENCES assure (id)');
        $this->addSql('ALTER TABLE intermediaire_souscripteur ADD CONSTRAINT FK_BB664C98F9D711AE FOREIGN KEY (intermediaire_id) REFERENCES intermediaire (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE intermediaire_souscripteur ADD CONSTRAINT FK_BB664C98A0B466D6 FOREIGN KEY (souscripteur_id) REFERENCES souscripteur (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE listing ADD CONSTRAINT FK_CB0048D4A0B466D6 FOREIGN KEY (souscripteur_id) REFERENCES souscripteur (id)');
        $this->addSql('ALTER TABLE listing ADD CONSTRAINT FK_CB0048D4F9D711AE FOREIGN KEY (intermediaire_id) REFERENCES intermediaire (id)');
        $this->addSql('ALTER TABLE listing_assure ADD CONSTRAINT FK_2381EDFAD4619D1A FOREIGN KEY (listing_id) REFERENCES listing (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE listing_assure ADD CONSTRAINT FK_2381EDFA1F4BE942 FOREIGN KEY (assure_id) REFERENCES assure (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE beneficiaire DROP FOREIGN KEY FK_B140D8021F4BE942');
        $this->addSql('ALTER TABLE listing_assure DROP FOREIGN KEY FK_2381EDFA1F4BE942');
        $this->addSql('ALTER TABLE assure DROP FOREIGN KEY FK_C779CC29F9D711AE');
        $this->addSql('ALTER TABLE intermediaire_souscripteur DROP FOREIGN KEY FK_BB664C98F9D711AE');
        $this->addSql('ALTER TABLE listing DROP FOREIGN KEY FK_CB0048D4F9D711AE');
        $this->addSql('ALTER TABLE listing_assure DROP FOREIGN KEY FK_2381EDFAD4619D1A');
        $this->addSql('ALTER TABLE intermediaire_souscripteur DROP FOREIGN KEY FK_BB664C98A0B466D6');
        $this->addSql('ALTER TABLE listing DROP FOREIGN KEY FK_CB0048D4A0B466D6');
        $this->addSql('DROP TABLE assure');
        $this->addSql('DROP TABLE beneficiaire');
        $this->addSql('DROP TABLE intermediaire');
        $this->addSql('DROP TABLE intermediaire_souscripteur');
        $this->addSql('DROP TABLE listing');
        $this->addSql('DROP TABLE listing_assure');
        $this->addSql('DROP TABLE souscripteur');
    }
}
