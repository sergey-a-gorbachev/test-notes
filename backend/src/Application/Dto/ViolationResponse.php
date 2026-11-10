<?php

declare(strict_types=1);

namespace App\Application\Dto;

use OpenApi\Attributes as OA;

#[OA\Schema(description: 'Ошибка проверки поля.')]
final readonly class ViolationResponse
{
    public function __construct(
        #[OA\Property(example: 'title')] public string $field,
        #[OA\Property(example: 'Title is required.')] public string $message,
    ) {
    }
}
