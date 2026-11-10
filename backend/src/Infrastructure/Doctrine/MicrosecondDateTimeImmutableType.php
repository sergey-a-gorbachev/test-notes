<?php

declare(strict_types=1);

namespace App\Infrastructure\Doctrine;

use DateTimeImmutable;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DateTimeImmutableType;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Exception;

final class MicrosecondDateTimeImmutableType extends DateTimeImmutableType
{
    public const NAME = 'datetime_immutable_microseconds';

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof DateTimeImmutable) {
            throw InvalidType::new($value, self::class, ['null', DateTimeImmutable::class]);
        }

        return $value->format('Y-m-d H:i:s.u');
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?DateTimeImmutable
    {
        if ($value === null || $value instanceof DateTimeImmutable) {
            return $value;
        }

        foreach (['!Y-m-d H:i:s.u', '!Y-m-d H:i:s'] as $format) {
            $dateTime = DateTimeImmutable::createFromFormat($format, $value);
            if ($dateTime !== false) {
                return $dateTime;
            }
        }

        throw InvalidFormat::new($value, self::class, 'Y-m-d H:i:s.u', new Exception('Некорректная дата.'));
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
