<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Pgsql\Container;

use Laminas\ServiceManager\Factory\FactoryInterface;
use PhpDb\Adapter\Adapter;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Pgsql\Platform;
use PhpDb\Adapter\Profiler\ProfilerInterface;
use PhpDb\ResultSet\ResultSetInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class AdapterInterfaceFactory implements FactoryInterface
{
    /**
     * Create db adapter service
     *
     * @param array|null         $options
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null
    ): Adapter {
        $resultSetPrototype = $container->has(ResultSetInterface::class)
            ? $container->get(ResultSetInterface::class)
            : null;

        $profiler = $container->get(ProfilerInterface::class);

        return new Adapter(
            $container->get(DriverInterface::class),
            $container->get(Platform\Postgresql::class),
            $resultSetPrototype,
            $profiler
        );
    }
}
