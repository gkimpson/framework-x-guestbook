<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;
use Exception;

final class Entry
{
    public function __construct(
        public readonly int                $id,
        public readonly string             $name,
        public readonly string             $message,
        public readonly ?DateTimeImmutable $createdAt,
    ) {
    }

    /**
     * Build an Entry from a raw MySQL result row.
     *
     * @param array<string, mixed> $row
     * @throws Exception
     */
    public static function fromRow(array $row): self
    {
        return new self(
            (int) $row['id'],
            (string) $row['name'],
            (string) $row['message'],
            isset($row['created_at']) ? new DateTimeImmutable((string) $row['created_at']) : null,
        );
    }
}
