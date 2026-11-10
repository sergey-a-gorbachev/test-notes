<?php

declare(strict_types=1);

namespace App\Application\Dto;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(description: 'Данные для создания или изменения заметки.')]
final readonly class NoteRequest
{
    public function __construct(
        #[OA\Property(description: 'Название заметки.', example: 'Список покупок')]
        #[Assert\NotBlank(message: 'Title is required.', normalizer: 'trim')]
        #[Assert\Length(max: 160, maxMessage: 'Title must not exceed {{ limit }} characters.')]
        public string $title,
        #[OA\Property(description: 'Содержимое заметки.', example: 'Молоко и хлеб')]
        #[Assert\Length(max: 10000, maxMessage: 'Content must not exceed {{ limit }} characters.')]
        public string $content = '',
        #[OA\Property(description: 'Версия заметки для оптимистической блокировки.', example: 1, nullable: true)]
        #[Assert\Positive(message: 'Version must be a positive integer.')]
        public ?int $version = null,
    ) {
    }
}
