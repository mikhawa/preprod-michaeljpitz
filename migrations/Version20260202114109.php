<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260202114109 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Relation Article-Category : ManyToOne vers ManyToMany avec migration des données existantes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE article_category (article_id INT UNSIGNED NOT NULL, category_id INT UNSIGNED NOT NULL, INDEX IDX_53A4EDAA7294869C (article_id), INDEX IDX_53A4EDAA12469DE2 (category_id), PRIMARY KEY (article_id, category_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE article_category ADD CONSTRAINT FK_53A4EDAA7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_category ADD CONSTRAINT FK_53A4EDAA12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE');
        $this->addSql('INSERT INTO article_category (article_id, category_id) SELECT id, category_id FROM article WHERE category_id IS NOT NULL');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY `FK_23A0E6612469DE2`');
        $this->addSql('DROP INDEX IDX_23A0E6612469DE2 ON article');
        $this->addSql('ALTER TABLE article DROP category_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_category DROP FOREIGN KEY FK_53A4EDAA7294869C');
        $this->addSql('ALTER TABLE article_category DROP FOREIGN KEY FK_53A4EDAA12469DE2');
        $this->addSql('DROP TABLE article_category');
        $this->addSql('ALTER TABLE article ADD category_id INT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT `FK_23A0E6612469DE2` FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('CREATE INDEX IDX_23A0E6612469DE2 ON article (category_id)');
    }
}
