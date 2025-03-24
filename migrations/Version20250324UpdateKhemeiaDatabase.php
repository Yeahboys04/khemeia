<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250324UpdateKhemeiaDatabase extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update Khemeia database structure with new columns and tables';
    }

    public function up(Schema $schema): void
    {
        // Add creation_date column to storagecard table
        $this->addSql('ALTER TABLE storagecard ADD COLUMN creation_date DATETIME DEFAULT NULL');

        // Create incompatibility_request table if not exists
        $this->addSql('CREATE TABLE IF NOT EXISTS incompatibility_request (
            id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL COMMENT \'Identifiant unique de la demande\',
            id_requester BIGINT UNSIGNED DEFAULT NULL COMMENT \'Utilisateur qui a fait la demande\',
            id_product BIGINT UNSIGNED DEFAULT NULL COMMENT \'Produit à stocker\',
            id_shelvingunit BIGINT UNSIGNED DEFAULT NULL COMMENT \'Emplacement souhaité\',
            incompatible_with TEXT DEFAULT NULL COMMENT \'Liste des produits incompatibles déjà présents à cet emplacement\',
            reason TEXT DEFAULT NULL COMMENT \'Justification de la demande\',
            is_urgent TINYINT(1) DEFAULT NULL COMMENT \'Indique si la demande est urgente (réponse nécessaire sous 24h)\',
            request_date DATETIME DEFAULT NULL COMMENT \'Date et heure de la demande\',
            status VARCHAR(20) DEFAULT NULL COMMENT \'Statut de la demande: pending, approved, rejected\',
            response_date DATETIME DEFAULT NULL COMMENT \'Date et heure de la réponse\',
            id_responder BIGINT UNSIGNED DEFAULT NULL COMMENT \'Administrateur ayant traité la demande\',
            response_comment TEXT DEFAULT NULL COMMENT \'Commentaire de l\'administrateur\',
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'Demandes de dérogation pour le stockage de produits chimiques incompatibles\'');

        // Add indexes for incompatibility_request
        $this->addSql('CREATE INDEX idx_incomp_req_requester ON incompatibility_request (id_requester)');
        $this->addSql('CREATE INDEX idx_incomp_req_product ON incompatibility_request (id_product)');
        $this->addSql('CREATE INDEX idx_incomp_req_shelvingunit ON incompatibility_request (id_shelvingunit)');
        $this->addSql('CREATE INDEX idx_incomp_req_responder ON incompatibility_request (id_responder)');
        $this->addSql('CREATE INDEX idx_incomp_req_status ON incompatibility_request (status)');
        $this->addSql('CREATE INDEX idx_incomp_req_request_date ON incompatibility_request (request_date)');
        $this->addSql('CREATE INDEX idx_incomp_req_response_date ON incompatibility_request (response_date)');
        $this->addSql('CREATE INDEX idx_incomp_req_is_urgent ON incompatibility_request (is_urgent)');

        // Add foreign key constraints
        $this->addSql('ALTER TABLE incompatibility_request 
            ADD CONSTRAINT fk_incomp_req_product 
            FOREIGN KEY (id_product) 
            REFERENCES chimicalproduct(id_chimicalProduct) 
            ON UPDATE CASCADE');

        $this->addSql('ALTER TABLE incompatibility_request 
            ADD CONSTRAINT fk_incomp_req_requester 
            FOREIGN KEY (id_requester) 
            REFERENCES user(id_user) 
            ON UPDATE CASCADE');

        $this->addSql('ALTER TABLE incompatibility_request 
            ADD CONSTRAINT fk_incomp_req_responder 
            FOREIGN KEY (id_responder) 
            REFERENCES user(id_user) 
            ON UPDATE CASCADE');

        $this->addSql('ALTER TABLE incompatibility_request 
            ADD CONSTRAINT fk_incomp_req_shelvingunit 
            FOREIGN KEY (id_shelvingunit) 
            REFERENCES shelvingunit(id_shelvingUnit) 
            ON UPDATE CASCADE');

        // Add check constraint for status
        $this->addSql('ALTER TABLE incompatibility_request 
            ADD CONSTRAINT chk_incomp_req_status 
            CHECK (status IN (\'pending\', \'approved\', \'rejected\'))');
    }

    public function down(Schema $schema): void
    {
        // Remove foreign key constraints first
        $this->addSql('ALTER TABLE incompatibility_request 
            DROP FOREIGN KEY fk_incomp_req_product');
        $this->addSql('ALTER TABLE incompatibility_request 
            DROP FOREIGN KEY fk_incomp_req_requester');
        $this->addSql('ALTER TABLE incompatibility_request 
            DROP FOREIGN KEY fk_incomp_req_responder');
        $this->addSql('ALTER TABLE incompatibility_request 
            DROP FOREIGN KEY fk_incomp_req_shelvingunit');

        // Drop indexes
        $this->addSql('DROP INDEX idx_incomp_req_requester ON incompatibility_request');
        $this->addSql('DROP INDEX idx_incomp_req_product ON incompatibility_request');
        $this->addSql('DROP INDEX idx_incomp_req_shelvingunit ON incompatibility_request');
        $this->addSql('DROP INDEX idx_incomp_req_responder ON incompatibility_request');
        $this->addSql('DROP INDEX idx_incomp_req_status ON incompatibility_request');
        $this->addSql('DROP INDEX idx_incomp_req_request_date ON incompatibility_request');
        $this->addSql('DROP INDEX idx_incomp_req_response_date ON incompatibility_request');
        $this->addSql('DROP INDEX idx_incomp_req_is_urgent ON incompatibility_request');

        // Drop the incompatibility_request table
        $this->addSql('DROP TABLE incompatibility_request');

        // Remove creation_date column from storagecard
        $this->addSql('ALTER TABLE storagecard DROP COLUMN creation_date');
    }
}

