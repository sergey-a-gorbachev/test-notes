<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Model\Note;

interface NoteRepositoryInterface
{
    public function findAll(): array;

    public function find(int $id): ?Note;

    public function create(string $title, string $content): Note;

    public function update(int $id, string $title, string $content, ?int $expectedVersion = null): ?Note;

    public function delete(int $id): bool;
}
