<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260201195226 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout des champs reset_password_token et reset_password_requested_at à la table user';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD reset_password_token VARCHAR(64) DEFAULT NULL, ADD reset_password_requested_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` DROP reset_password_token, DROP reset_password_requested_at');
    }
}
