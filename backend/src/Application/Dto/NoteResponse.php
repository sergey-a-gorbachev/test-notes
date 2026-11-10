<?php

declare(strict_types=1);

namespace App\Application\Dto;

use App\Domain\Model\Note;
use OpenApi\Attributes as OA;

#[OA\Schema(description: 'Заметка.')]
final readonly class NoteResponse
{
    public function __construct(
        #[OA\Property(description: 'Идентификатор заметки.', example: 1)]
        public int $id,
        #[OA\Property(description: 'Название заметки.', example: 'Список покупок')]
        public string $title,
        #[OA\Property(description: 'Содержимое заметки.', example: 'Молоко и хлеб')]
        public string $content,
        #[OA\Property(property: 'created_at', description: 'Дата создания.', format: 'date-time')]
        public string $created_at,
        #[OA\Property(property: 'updated_at', description: 'Дата последнего изменения.', format: 'date-time')]
        public string $updated_at,
        #[OA\Property(description: 'Версия заметки.', example: 1)]
        public int $version,
    ) {
    }

    public static function fromDomain(Note $note): self
    {
        return new self(
            $note->id(),
            $note->title(),
            $note->content(),
            $note->createdAt()->format(DATE_ATOM),
            $note->updatedAt()->format(DATE_ATOM),
            $note->version(),
        );
    }
}
