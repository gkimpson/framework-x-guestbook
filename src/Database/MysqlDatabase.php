<?php

declare(strict_types=1);

namespace App\Database;

use React\Mysql\MysqlClient;
use React\Promise\PromiseInterface;

final class MysqlDatabase implements DatabaseInterface
{
    public function __construct(private readonly MysqlClient $client)
    {
    }

    /**
     * Executes a database query with the given SQL statement and parameters.
     *
     * @param string $sql The SQL query to execute.
     * @param array $params Optional array of parameters to bind to the query.
     * @return PromiseInterface A promise representing the result of the query.
     */
    public function query(string $sql, array $params = []): PromiseInterface
    {
        return $this->client->query($sql, $params);
    }
}
