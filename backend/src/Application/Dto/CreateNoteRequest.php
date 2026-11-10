<?php

declare(strict_types=1);

namespace App\Application\Dto;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(description: 'Data required to create a note.')]
final readonly class CreateNoteRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Title is required.', normalizer: 'trim')]
        #[Assert\Length(max: 160, maxMessage: 'Title must not exceed {{ limit }} characters.')]
        public string $title,
        #[Assert\Length(max: 10000, maxMessage: 'Content must not exceed {{ limit }} characters.')]
        public string $content = '',
    ) {
    }
}
