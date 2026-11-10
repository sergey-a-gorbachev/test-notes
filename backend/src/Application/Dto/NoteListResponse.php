<?php

declare(strict_types=1);

namespace App\Application\Dto;

use OpenApi\Attributes as OA;

#[OA\Schema(description: 'Список заметок.')]
final readonly class NoteListResponse
{
    public function __construct(
        #[OA\Property(type: 'array', items: new OA\Items(ref: '#/components/schemas/NoteResponse'))]
        public array $items,
    ) {
    }
}
