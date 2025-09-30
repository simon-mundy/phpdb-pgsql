<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Pgsql\Container;

use Laminas\ServiceManager\Factory\FactoryInterface;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Pgsql\Driver\Pdo;
use Psr\Container\ContainerInterface;

final class PdoDriverInterfaceFactory implements FactoryInterface
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null
    ): DriverInterface&PdoDriverInterface {
        // todo: pull deps from container
        return new Pdo\Pdo($container->get('config')['db']);
    }
}
