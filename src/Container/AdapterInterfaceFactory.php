<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Pgsql\Container;

use Laminas\ServiceManager\ServiceManager;
use PhpDb\Adapter\Adapter;
use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Adapter\Pgsql;
use PhpDb\Adapter\Profiler\ProfilerInterface;
use PhpDb\ResultSet\ResultSetInterface;
use Psr\Container\ContainerInterface;

use function sprintf;

class AdapterInterfaceFactory
{
    public function __invoke(
        ContainerInterface|ServiceManager $container,
        string $requestedName,
    ): AdapterInterface&Adapter {
        $config        = $container->get('config') ?? [];
        $adapterConfig = null;

        if (
            isset($config[AdapterInterface::class]['connection'])
            || isset($config[Adapter::class]['connection'])
        ) {
            $adapterConfig = $config[AdapterInterface::class] ?? $config[Adapter::class];
        } else {
            $adapterConfig = $config['adapters'][$requestedName] ?? $config;
        }

        if ($adapterConfig === []) {
            throw Pgsql\Exception\ContainerException::forServiceFailure(
                AdapterInterface::class,
                sprintf(
                    'No configuration found for adapter "%s"',
                    $requestedName
                )
            );
        }

        /** @var class-string<DriverInterface>|class-string<PdoDriverInterface>|null  */
        $driverClass = $adapterConfig['driver'] ?? null;

        if ($driverClass === null || ! $container->has($driverClass)) {
            throw Pgsql\Exception\ContainerException::forServiceFailure(
                AdapterInterface::class,
                sprintf(
                    'Invalid or missing driver provided for adapter "%s"',
                    $requestedName
                )
            );
        }

        /** @var DriverInterface|PdoDriverInterface */
        $driver = $container->build($driverClass, $adapterConfig);

        /** @var PlatformInterface&AdapterPlatform */
        $adapterPlatform = $container->build(PlatformInterface::class, ['driver' => $driver]);

        /** @var ProfilerInterface|null */
        $profilerInterface = $container->has(ProfilerInterface::class)
            ?   $container->get(ProfilerInterface::class)
            : null;

        /** @var ResultSetInterface|null */
        $queryResultSetPrototype = $container->has(ResultSetInterface::class)
            ? $container->get(ResultSetInterface::class)
            : null;

        return match(true) {
            $queryResultSetPrototype !== null && $profilerInterface !== null => new Adapter(
                driver: $driver,
                platform: $adapterPlatform,
                profiler: $profilerInterface,
                queryResultSetPrototype: $queryResultSetPrototype,
            ),
            $queryResultSetPrototype !== null => new Adapter(
                driver: $driver,
                platform: $adapterPlatform,
                queryResultSetPrototype: $queryResultSetPrototype,
            ),
            $profilerInterface !== null => new Adapter(
                driver: $driver,
                platform: $adapterPlatform,
                profiler: $profilerInterface,
            ),
            default => new Adapter(
                driver: $driver,
                platform: $adapterPlatform,
            ),
        };
    }
}
