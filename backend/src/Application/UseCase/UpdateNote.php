<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\Dto\UpdateNoteRequest;
use App\Application\Dto\NoteResponse;
use App\Domain\Exception\NoteNotFoundException;
use App\Domain\Repository\NoteRepositoryInterface;

final readonly class UpdateNote
{
    public function __construct(private NoteRepositoryInterface $notes) {}

    public function __invoke(int $id, UpdateNoteRequest $request): NoteResponse
    {
        $note = $this->notes->find($id) ?? throw new NoteNotFoundException($id);

        return NoteResponse::fromDomain(
            $this->notes->update($note, trim($request->title), $request->content, $request->version),
        );
    }
}
