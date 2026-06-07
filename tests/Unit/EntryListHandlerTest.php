<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Database\DatabaseInterface;
use App\Handler\EntryListHandler;
use App\Repository\EntryRepository;
use PHPUnit\Framework\TestCase;
use React\Mysql\MysqlResult;

use Throwable;
use function React\Async\await;
use function React\Promise\resolve;

class EntryListHandlerTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test_empty_list_returns_200_html(): void
    {
        $result = new MysqlResult();
        $result->resultRows = [];

        $db = $this->createMock(DatabaseInterface::class);
        $db->method('query')->willReturn(resolve($result));

        $response = await((new EntryListHandler(new EntryRepository($db)))());

        $body = (string) $response->getBody();

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('text/html', $response->getHeaderLine('Content-Type'));
        self::assertStringContainsString('No entries yet', $body);
    }

    /**
     * @throws Throwable
     */
    public function test_entries_are_rendered(): void
    {
        $result = new MysqlResult();
        $result->resultRows = [
            ['id' => 1, 'name' => 'Gavin', 'message' => 'Hello world', 'created_at' => '2026-06-02 10:00:00'],
        ];

        $db = $this->createMock(DatabaseInterface::class);
        $db->method('query')->willReturn(resolve($result));

        $body = (string) await((new EntryListHandler(new EntryRepository($db)))())->getBody();

        self::assertStringContainsString('Gavin', $body);
        self::assertStringContainsString('Hello world', $body);
        self::assertStringContainsString('2026-06-02 10:00', $body);
    }

    /**
     * @throws Throwable
     */
    public function test_xss_is_escaped(): void
    {
        $result = new MysqlResult();
        $result->resultRows = [
            ['id' => 1, 'name' => '<script>alert(1)</script>', 'message' => '" onload="evil()"', 'created_at' => null],
        ];

        $db = $this->createMock(DatabaseInterface::class);
        $db->method('query')->willReturn(resolve($result));

        $body = (string) await((new EntryListHandler(new EntryRepository($db)))())->getBody();

        self::assertStringNotContainsString('<script>', $body);
        self::assertStringContainsString('&lt;script&gt;', $body);
        self::assertStringNotContainsString('" onload="', $body);
    }
}
