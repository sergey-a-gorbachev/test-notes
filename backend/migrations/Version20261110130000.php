<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261110130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Создаёт таблицу заметок и индекс сортировки';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE notes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(160) NOT NULL, content CLOB NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, version INTEGER DEFAULT 1 NOT NULL)');
        $this->addSql('CREATE INDEX idx_notes_updated_at ON notes (updated_at, id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE notes');
    }
}
