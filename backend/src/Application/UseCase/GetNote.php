<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\Dto\NoteResponse;
use App\Domain\Exception\NoteNotFoundException;
use App\Domain\Repository\NoteRepositoryInterface;

final readonly class GetNote
{
    public function __construct(private NoteRepositoryInterface $notes) {}

    public function __invoke(int $id): NoteResponse
    {
        return NoteResponse::fromDomain($this->notes->find($id) ?? throw new NoteNotFoundException($id));
    }
}
