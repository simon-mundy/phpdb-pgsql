<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Pgsql\Driver\Pgsql;

use PhpDb\Adapter\Exception\InvalidArgumentException;

use function filter_var_array;
use function strtolower;

use const FILTER_FLAG_EMPTY_STRING_NULL;
use const FILTER_FLAG_STRIP_HIGH;
use const FILTER_FLAG_STRIP_LOW;
use const FILTER_NULL_ON_FAILURE;
use const FILTER_SANITIZE_ENCODED;
use const FILTER_SANITIZE_URL;
use const FILTER_VALIDATE_INT;

class PgsqlConfig
{
    public function __invoke(array $parameters): array
    {
        $connectionParameters = [];
        foreach ($parameters as $name => $value) {
            $name = match (strtolower($name)) {
                'host', 'hostname' => 'host',
                'user', 'username' => 'user',
                'password', 'passwd', 'pw' => 'password',
                'database', 'dbname', 'db', 'schema' => 'dbname',
                'port' => 'port',
                'socket' => 'socket',
                default => throw new InvalidArgumentException(
                    'Unknown connection parameter "' . $name . '"'
                ),
            };
            $connectionParameters[$name] = $value;
        }

        $connectionFilters = [
            'host'     => [
                'filter' => FILTER_SANITIZE_URL,
                'flags'  => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_EMPTY_STRING_NULL,
            ],
            'user'     => [
                'filter' => FILTER_SANITIZE_ENCODED,
                'flags'  => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_EMPTY_STRING_NULL,
            ],
            'password' => [
                'filter' => FILTER_SANITIZE_ENCODED,
                'flags'  => FILTER_FLAG_EMPTY_STRING_NULL,
            ],
            'database' => [
                'filter' => FILTER_SANITIZE_ENCODED,
                'flags'  => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_EMPTY_STRING_NULL,
            ],
            'socket'   => [
                'filter' => FILTER_SANITIZE_ENCODED,
                'flags'  => FILTER_FLAG_EMPTY_STRING_NULL,
            ],
            'port'     => [
                'filter' => FILTER_VALIDATE_INT,
                'flags'  => FILTER_NULL_ON_FAILURE,
            ],
        ];

        return filter_var_array($connectionParameters, $connectionFilters);
    }
}
