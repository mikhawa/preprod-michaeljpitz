<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260426143948 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE page_category (page_id INT UNSIGNED NOT NULL, category_id INT UNSIGNED NOT NULL, INDEX IDX_86D31EE1C4663E4 (page_id), INDEX IDX_86D31EE112469DE2 (category_id), PRIMARY KEY (page_id, category_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE page_category ADD CONSTRAINT FK_86D31EE1C4663E4 FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE page_category ADD CONSTRAINT FK_86D31EE112469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_page DROP FOREIGN KEY `FK_category_page_category`');
        $this->addSql('ALTER TABLE category_page DROP FOREIGN KEY `FK_category_page_page`');
        $this->addSql('DROP TABLE category_page');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category_page (category_id INT UNSIGNED NOT NULL, page_id INT UNSIGNED NOT NULL, INDEX IDX_F7D875CFC4663E4 (page_id), INDEX IDX_F7D875CF12469DE2 (category_id), PRIMARY KEY (category_id, page_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE category_page ADD CONSTRAINT `FK_category_page_category` FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE category_page ADD CONSTRAINT `FK_category_page_page` FOREIGN KEY (page_id) REFERENCES page (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE page_category DROP FOREIGN KEY FK_86D31EE1C4663E4');
        $this->addSql('ALTER TABLE page_category DROP FOREIGN KEY FK_86D31EE112469DE2');
        $this->addSql('DROP TABLE page_category');
    }
}
