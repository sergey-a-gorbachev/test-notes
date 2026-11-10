<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use RuntimeException;

final class NoteVersionConflictException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Версия заметки устарела.');
    }
}
