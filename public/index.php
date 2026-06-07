<?php

declare(strict_types=1);

use App\Database\DatabaseInterface;
use App\Database\MysqlDatabase;
use App\Handler\EntryCreateHandler;
use App\Handler\EntryListHandler;
use FrameworkX\App;
use FrameworkX\Container;
use React\Mysql\MysqlClient;

require __DIR__ . '/../vendor/autoload.php';

$container = new Container([
    MysqlClient::class => static fn (string $MYSQL_URI) => new MysqlClient($MYSQL_URI),
    DatabaseInterface::class => static fn (MysqlClient $client) => new MysqlDatabase($client),
]);

$app = new App($container);

$app->get('/', EntryListHandler::class);
$app->post('/entries', EntryCreateHandler::class);

$app->run();
