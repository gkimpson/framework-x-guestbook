<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database\DatabaseInterface;
use App\Model\Entry;
use React\Mysql\MysqlResult;
use React\Promise\PromiseInterface;

final class EntryRepository
{
    public function __construct(private readonly DatabaseInterface $db)
    {
    }

    /**
     * @return PromiseInterface<list<Entry>> Resolves newest first.
     */
    public function all(): PromiseInterface
    {
        return $this->db->query(
            'SELECT id, name, message, created_at FROM entries ORDER BY created_at DESC, id DESC'
        )->then(fn(MysqlResult $result) => array_map(Entry::fromRow(...), $result->resultRows));
    }

    /** @return PromiseInterface<Entry> */
    public function create(string $name, string $message): PromiseInterface
    {
        return $this->db->query(
            'INSERT INTO entries (name, message) VALUES (?, ?)',
            [$name, $message],
        )->then(fn(MysqlResult $result) => new Entry((int) $result->insertId, $name, $message, null));
    }
}
