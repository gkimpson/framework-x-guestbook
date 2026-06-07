<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Database\DatabaseInterface;
use App\Model\Entry;
use App\Repository\EntryRepository;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use React\Mysql\MysqlResult;

use Throwable;
use function React\Async\await;
use function React\Promise\resolve;

class EntryRepositoryTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_all_maps_rows_to_entries(): void
    {
        $result = new MysqlResult();
        $result->resultRows = [
            ['id' => 1, 'name' => 'Gavin', 'message' => 'Hello', 'created_at' => '2026-06-02 10:00:00'],
            ['id' => 2, 'name' => 'Bo', 'message' => 'Hi there', 'created_at' => '2026-06-02 11:00:00'],
        ];

        $db = $this->createMock(DatabaseInterface::class);
        $db->method('query')->willReturn(resolve($result));

        $entries = await((new EntryRepository($db))->all());

        self::assertCount(2, $entries);
        self::assertContainsOnlyInstancesOf(Entry::class, $entries);
        self::assertSame('Gavin', $entries[0]->name);
        self::assertSame('Hi there', $entries[1]->message);
    }

    public function test_from_row_maps_to_entry(): void
    {
        $entry = Entry::fromRow([
            'id' => 7,
            'name' => 'Cleo',
            'message' => 'Nice place',
            'created_at' => '2026-06-02 09:00:00',
        ]);

        self::assertSame(7, $entry->id);
        self::assertSame('Cleo', $entry->name);
        self::assertSame('Nice place', $entry->message);
        self::assertInstanceOf(DateTimeImmutable::class, $entry->createdAt);
        self::assertSame('2026-06-02 09:00:00', $entry->createdAt->format('Y-m-d H:i:s'));
    }

    /**
     * @throws Throwable
     */
    public function test_create_returns_entry_with_insert_id(): void
    {
        $result = new MysqlResult();
        $result->insertId = 42;

        $db = $this->createMock(DatabaseInterface::class);
        $db->method('query')->willReturn(resolve($result));

        $entry = await((new EntryRepository($db))->create('Gavin', 'Hello'));

        self::assertSame(42, $entry->id);
        self::assertSame('Gavin', $entry->name);
        self::assertSame('Hello', $entry->message);
        self::assertNull($entry->createdAt);
    }
}
