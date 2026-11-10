<?php

declare(strict_types=1);

namespace App\Application\Dto;

use OpenApi\Attributes as OA;

#[OA\Schema(description: 'Ошибка выполнения запроса.')]
final readonly class ErrorResponse
{
    public function __construct(
        #[OA\Property(example: 'Проверка данных не пройдена.')]
        public string $error,
        #[OA\Property(type: 'array', items: new OA\Items(
            properties: [
                new OA\Property(property: 'field', type: 'string', example: 'title'),
                new OA\Property(property: 'message', type: 'string', example: 'Укажите название заметки.'),
            ],
            type: 'object',
        ))]
        public array $violations = [],
    ) {
    }
}
