<?php

declare(strict_types=1);

namespace App\Application\Dto;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(description: 'Данные для создания заметки.')]
final readonly class CreateNoteRequest
{
    public function __construct(
        #[OA\Property(description: 'Название заметки.', example: 'Список покупок')]
        #[Assert\NotBlank(message: 'Укажите название заметки.', normalizer: 'trim')]
        #[Assert\Length(max: 160, maxMessage: 'Название не должно превышать {{ limit }} символов.')]
        public string $title,
        #[OA\Property(description: 'Содержимое заметки.', example: 'Молоко и хлеб')]
        #[Assert\Length(max: 10000, maxMessage: 'Текст не должен превышать {{ limit }} символов.')]
        public string $content = '',
    ) {
    }
}
