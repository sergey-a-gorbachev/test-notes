<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Model\Note;

interface NoteRepositoryInterface
{
    /** @return list<Note> */
    public function findAll(): array;

    public function find(int $id): ?Note;

    public function create(string $title, string $content): Note;

    public function update(Note $note, string $title, string $content, int $expectedVersion): Note;

    public function delete(int $id, int $expectedVersion): bool;
}
