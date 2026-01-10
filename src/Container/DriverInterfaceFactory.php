<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Pgsql\Container;

use Laminas\ServiceManager\ServiceManager;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Pgsql;
use Psr\Container\ContainerInterface;

final class DriverInterfaceFactory
{
    public function __invoke(
        ContainerInterface&ServiceManager $container,
        string $requestedName,
        ?array $options = null
    ): DriverInterface {
        if (! $options['connection']) {
            throw Pgsql\Exception\ContainerException::forServiceFailure(
                Pgsql\Driver::class,
                self::class . ' can only be used via the ServiceManager\'s build() method
                with connection parameters passed via $options["connection"]'
            );
        }
        $connection = $container->build(Pgsql\Connection::class, $options);
        return new Pgsql\Driver(
            connection:$connection,
            statementPrototype: $container->build(Pgsql\Statement::class, ['options' => $options['options'] ?? []]),
            resultPrototype: new Pgsql\Result(),
        );
    }
}
