<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Database\DatabaseInterface;
use App\Handler\EntryCreateHandler;
use App\Repository\EntryRepository;
use PHPUnit\Framework\TestCase;
use React\Http\Message\ServerRequest;
use React\Mysql\MysqlResult;

use React\Promise\PromiseInterface;
use Throwable;
use function React\Async\await;
use function React\Promise\resolve;

class EntryCreateHandlerTest extends TestCase
{
    public function test_empty_fields_return_422(): void
    {
        $db = $this->createMock(DatabaseInterface::class);
        $db->expects($this->never())->method('query');

        $handler = new EntryCreateHandler(new EntryRepository($db));
        $request = (new ServerRequest('POST', 'https://example.com/entries'))
            ->withParsedBody(['name' => '   ', 'message' => '']);

        $response = $handler($request);
        $body = (string) $response->getBody();

        self::assertSame(422, $response->getStatusCode());
        self::assertStringContainsString('text/html', $response->getHeaderLine('Content-Type'));
        self::assertStringContainsString('Name and message are required.', $body);
        self::assertStringContainsString('href="/"', $body);
    }

    public function test_name_over_limit_returns_422(): void
    {
        $db = $this->createMock(DatabaseInterface::class);
        $db->expects($this->never())->method('query');

        $handler = new EntryCreateHandler(new EntryRepository($db));
        $request = (new ServerRequest('POST', 'https://example.com/entries'))
            ->withParsedBody(['name' => str_repeat('a', 101), 'message' => 'Hello']);

        $response = $handler($request);

        self::assertSame(422, $response->getStatusCode());
        self::assertStringContainsString('at most 100 characters', (string) $response->getBody());
    }

    public function test_message_over_limit_returns_422(): void
    {
        $db = $this->createMock(DatabaseInterface::class);
        $db->expects($this->never())->method('query');

        $handler = new EntryCreateHandler(new EntryRepository($db));
        $request = (new ServerRequest('POST', 'https://example.com/entries'))
            ->withParsedBody(['name' => 'Gavin', 'message' => str_repeat('x', 501)]);

        $response = $handler($request);

        self::assertSame(422, $response->getStatusCode());
        self::assertStringContainsString('at most 500 characters', (string) $response->getBody());
    }

    /**
     * @throws Throwable
     */
    public function test_valid_submission_redirects_to_list(): void
    {
        $result = new MysqlResult();
        $result->insertId = 1;

        $db = $this->createMock(DatabaseInterface::class);
        $db->expects($this->once())
            ->method('query')
            ->willReturnCallback(function() use ($result): PromiseInterface {
                return resolve($result);
            });

        $handler = new EntryCreateHandler(new EntryRepository($db));
        $request = (new ServerRequest('POST', 'https://example.com/entries'))
            ->withParsedBody(['name' => 'Gavin', 'message' => 'Hello']);

        $response = await($handler($request));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/', $response->getHeaderLine('Location'));
    }
}
