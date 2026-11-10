<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Domain\Exception\NoteNotFoundException;
use App\Domain\Exception\NoteVersionConflictException;
use App\Domain\Repository\NoteRepositoryInterface;

final readonly class DeleteNote
{
    public function __construct(private NoteRepositoryInterface $notes)
    {
    }

    public function __invoke(int $id, int $expectedVersion): void
    {
        if ($this->notes->delete($id, $expectedVersion)) {
            return;
        }

        if ($this->notes->find($id) === null) {
            throw new NoteNotFoundException($id);
        }

        throw new NoteVersionConflictException();
    }
}
