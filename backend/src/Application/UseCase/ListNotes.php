<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\Dto\NoteListResponse;
use App\Application\Dto\NoteResponse;
use App\Domain\Repository\NoteRepositoryInterface;

final readonly class ListNotes
{
    public function __construct(private NoteRepositoryInterface $notes) {}

    public function __invoke(): NoteListResponse
    {
        return new NoteListResponse(array_map(NoteResponse::fromDomain(...), $this->notes->findAll()));
    }
}
