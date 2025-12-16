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
use PhpDb\ResultSet\ResultSetInterface;
use Psr\Container\ContainerInterface;

use function sprintf;

class AdapterInterfaceFactory
{
    public function __invoke(ContainerInterface $container): AdapterInterface
    {
        if (! $container->has('config')) {
            throw new ServiceNotFoundException(
                sprintf(
                    'Could not find service "config" in container %s',
                    $container::class
                )
            );
        }

        /** @var array $config */
        $config = $container->get('config');

        /** @var array $dbConfig */
        $dbConfig = $config['db'] ?? [];

        if (! isset($dbConfig['driver'])) {
            throw new RuntimeException(
                'Database configuration must contain a "driver" key'
            );
        }

        /** @var string $driver */
        $driver = $dbConfig['driver'];
        if (! $container->has($driver)) {
            throw new ServiceNotFoundException(
                sprintf(
                    'Could not find database driver service "%s" in container %s',
                    $driver,
                    $container::class
                )
            );
        }
        /** @var DriverInterface|PdoDriverInterface $driverInstance */
        $driverInstance = $container->get($driver);

        if (! $container->has(PlatformInterface::class)) {
            throw new ServiceNotFoundException(
                sprintf(
                    'Could not find service "%s" in container %s',
                    PlatformInterface::class,
                    $container::class
                )
            );
        }

        /** @var PlatformInterface $adapterPlatform */
        $adapterPlatform = $container->get(PlatformInterface::class);

        /** @var ProfilerInterface|null $profilerInstanceOrNull */
        $profilerInstanceOrNull = $container->has(ProfilerInterface::class)
            ? $container->get(ProfilerInterface::class)
            : null;

        if (! $container->has(ResultSetInterface::class)) {
            return new Adapter(
                driver: $driverInstance,
                platform: $adapterPlatform,
                profiler: $profilerInstanceOrNull
            );
        }

        return new Adapter(
            driver: $driverInstance,
            platform: $adapterPlatform,
            queryResultSetPrototype: $container->get(ResultSetInterface::class),
            profiler: $profilerInstanceOrNull
        );
    }
}
