<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use RuntimeException;

final class NoteNotFoundException extends RuntimeException
{
    public function __construct(int $id)
    {
        parent::__construct(sprintf('Заметка с идентификатором %d не найдена.', $id));
    }
}
