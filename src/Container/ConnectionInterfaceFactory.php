<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Pgsql\Container;

use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\Driver\ConnectionInterface;
use PhpDb\Adapter\Pgsql\Connection;
use PhpDb\Adapter\Exception\InvalidConnectionParametersException;
use Psr\Container\ContainerInterface;

final class ConnectionInterfaceFactory
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null
    ): ConnectionInterface {
        $connectionInfo = $options['connection']
            ?? $container->get('config')[AdapterInterface::class]['adapters'][$requestedName]['connection']
            ?? null;
        if ($connectionInfo === null || ! is_array($connectionInfo) || $connectionInfo === []) {
            throw new InvalidConnectionParametersException(
                'Connection configuration must be an array of parameters',
                $connectionInfo
            );
        }
        return new Connection($connectionInfo);
    }
}
