<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajout de la relation ManyToMany entre Page et Category.
 */
final class Version20260406000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la table de jonction category_page (relation ManyToMany Page ↔ Category)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE category_page (
                category_id INT UNSIGNED NOT NULL,
                page_id INT UNSIGNED NOT NULL,
                PRIMARY KEY (category_id, page_id),
                INDEX IDX_F7D875CF12469DE2 (category_id),
                INDEX IDX_F7D875CFC4663E4 (page_id),
                CONSTRAINT FK_category_page_category FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE,
                CONSTRAINT FK_category_page_page FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE CASCADE
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE category_page');
    }
}
