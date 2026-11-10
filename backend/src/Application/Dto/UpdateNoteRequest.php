<?php

declare(strict_types=1);

namespace App\Application\Dto;

use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[OA\Schema(description: 'Complete note replacement with its current version.')]
final readonly class UpdateNoteRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Title is required.', normalizer: 'trim')]
        #[Assert\Length(max: 160, maxMessage: 'Title must not exceed {{ limit }} characters.')]
        public string $title,
        #[Assert\Length(max: 10000, maxMessage: 'Content must not exceed {{ limit }} characters.')]
        public string $content,
        #[Assert\Positive(message: 'Version must be a positive integer.')]
        public int $version,
    ) {
    }
}
