<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Pgsql\Driver\Pdo;

use PhpDb\Adapter\Driver\DriverInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

final class DriverFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): DriverInterface
    {
        return new Pdo($container->get('config')['db']);
    }
}
