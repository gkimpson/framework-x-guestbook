<?php

declare(strict_types=1);

namespace App\Database;

use React\Promise\PromiseInterface;

interface DatabaseInterface
{

    /**
     * Executes a database query with the given SQL statement and parameters.
     *
     * @param string $sql The SQL query string to execute.
     * @param array $params An optional associative array of parameters to bind to the query.
     * @return PromiseInterface A promise representing the asynchronous result of the query execution.
     */
    public function query(string $sql, array $params = []): PromiseInterface;
}
