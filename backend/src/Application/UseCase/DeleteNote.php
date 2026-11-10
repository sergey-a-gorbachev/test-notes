<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Domain\Exception\NoteNotFoundException;
use App\Domain\Repository\NoteRepositoryInterface;

final readonly class DeleteNote
{
    public function __construct(private NoteRepositoryInterface $notes)
    {
    }

    public function __invoke(int $id, int $expectedVersion): void
    {
        $note = $this->notes->find($id) ?? throw new NoteNotFoundException($id);
        $this->notes->delete($note, $expectedVersion);
    }
}
