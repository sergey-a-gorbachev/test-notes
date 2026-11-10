<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\Dto\NoteRequest;
use App\Application\Dto\NoteResponse;
use App\Domain\Exception\NoteNotFoundException;
use App\Domain\Repository\NoteRepositoryInterface;

final readonly class UpdateNote
{
    public function __construct(private NoteRepositoryInterface $notes) {}

    public function __invoke(int $id, NoteRequest $request): NoteResponse
    {
        if ($this->notes->find($id) === null) {
            throw new NoteNotFoundException($id);
        }

        return NoteResponse::fromDomain(
            $this->notes->update($id, trim($request->title), $request->content, $request->version) ?? throw new NoteNotFoundException($id),
        );
    }
}
