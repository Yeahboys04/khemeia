<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour ajouter les fonctionnalités d'archivage d'utilisateurs
 */
final class Version20250317000000_ArchiveUsersMigration extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les champs is_archived et archived_date à la table user';
    }

    public function up(Schema $schema): void
    {
        // Ajouter les colonnes d'archivage
        $this->addSql('ALTER TABLE user ADD is_archived TINYINT(1) NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE user ADD archived_date DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Supprimer les colonnes d'archivage
        $this->addSql('ALTER TABLE user DROP COLUMN is_archived');
        $this->addSql('ALTER TABLE user DROP COLUMN archived_date');
    }
}