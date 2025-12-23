<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Pgsql\Container;

use Laminas\ServiceManager\ServiceManager;
use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Pgsql;
use Psr\Container\ContainerInterface;

final class DriverInterfaceFactory
{
    public function __invoke(
        ContainerInterface|ServiceManager $container,
        string $requestedName,
        ?array $options = null
    ): DriverInterface {
        $connectionParameters = $options['connection']
            ?? $container->get('config')[AdapterInterface::class]['adapters'][$requestedName]['connection']
            ?? [];
        $connection = $container->build(Pgsql\Connection::class, ['connection' => $connectionParameters]);
        return new Pgsql\Driver(
            connection:$connection,
            statementPrototype: $container->build(Pgsql\Statement::class, ['options' => $options['options'] ?? []]),
            resultPrototype: new Pgsql\Result(),
        );
    }
}
