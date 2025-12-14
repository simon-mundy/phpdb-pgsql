<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Pgsql\Container;

use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use PhpDb\Adapter\Adapter;
use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Exception\RuntimeException;
use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Adapter\Profiler\ProfilerInterface;
use PhpDb\Container\AdapterManager;
use PhpDb\ResultSet\ResultSetInterface;
use Psr\Container\ContainerInterface;

use function sprintf;

class AdapterInterfaceFactory
{
    /**
     * Create db adapter service
     *
     * @param array|null         $options
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): AdapterInterface {
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
