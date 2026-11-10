<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\Dto\NoteRequest;
use App\Application\Dto\NoteResponse;
use App\Domain\Repository\NoteRepositoryInterface;

final readonly class CreateNote
{
    public function __construct(private NoteRepositoryInterface $notes) {}

    public function __invoke(NoteRequest $request): NoteResponse
    {
        return NoteResponse::fromDomain($this->notes->create(trim($request->title), $request->content));
    }
}
