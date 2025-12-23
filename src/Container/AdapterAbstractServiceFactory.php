<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Pgsql\Container;

use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Laminas\ServiceManager\ServiceManager;
use PhpDb\Adapter\Adapter;
use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Adapter\Profiler\ProfilerInterface;
use PhpDb\ResultSet\ResultSetInterface;
use Psr\Container\ContainerInterface;

use function is_array;

/**
 * Database adapter abstract service factory.
 *
 * Allows configuring several database instances (such as writer and reader).
 *
 * @internal
 */
class AdapterAbstractServiceFactory implements AbstractFactoryInterface
{
    /** @var array */
    protected $config;

    /**
     * Can we create an adapter by the requested name?
     *
     * @param string $requestedName
     */
    public function canCreate(ContainerInterface $container, $requestedName): bool
    {
        $config = $this->getConfig($container);
        if ($config === []) {
            return false;
        }

        return isset($config[$requestedName])
            && is_array($config[$requestedName])
            && ! empty($config[$requestedName]);
    }

    /**
     * Create a DB adapter
     *
     * @param string $requestedName
     */
    public function __invoke(
        ContainerInterface|ServiceManager $container,
        $requestedName,
        ?array $options = null
    ): AdapterInterface&Adapter {
        /** @var string|null $driverClass */
        $driverClass = $this->config[$requestedName]['driver'] ?? null;
        if ($driverClass === null) {
            throw new ServiceNotCreatedException(
                sprintf('Cannot create adapter "%s"; no driver configured', $requestedName)
            );
        }

        /** @var DriverInterface|PdoDriverInterface $driver */
        $driver   = $container->build($driverClass, $this->config[$requestedName]);
        /** @var PlatformInterface&Pgsql\AdapterPlatform $platform */
        $platform = $container->build(PlatformInterface::class, ['driver' => $driver]);
        /** @var ResultSetInterface|null $resultSet */
        $resultSet = $container->has(ResultSetInterface::class)
            ? $container->build(ResultSetInterface::class)
            : null;
        /** @var ProfilerInterface|null $profiler */
        $profiler = $container->has(ProfilerInterface::class)
            ? $container->build(ProfilerInterface::class)
            : null;

        return match(true) {
            $resultSet !== null && $profiler !== null => new Adapter(
                driver:$driver,
                platform: $platform,
                queryResultSetPrototype: $resultSet,
                profiler: $profiler,
            ),
            $resultSet !== null => new Adapter(
                driver:$driver,
                platform: $platform,
                queryResultSetPrototype: $resultSet,
            ),
            $profiler !== null => new Adapter(
                driver:$driver,
                platform: $platform,
                profiler: $profiler,
            ),
            default => new Adapter(
                driver:$driver,
                platform: $platform,
            ),
        };
    }

    /**
     * Get db configuration, if any
     * todo: refactor to use PhpDb\ConfigProvider::ADAPTERS_CONFIG_KEY instead of hardcoding 'adapters'
     */
    protected function getConfig(ContainerInterface $container): array
    {
        if ($this->config !== null) {
            return $this->config;
        }

        if (! $container->has('config')) {
            $this->config = [];
            return $this->config;
        }

        $config = $container->get('config');
        if (
            ! isset($config[AdapterInterface::class])
            || ! is_array($config[AdapterInterface::class])
        ) {
            $this->config = [];
            return $this->config;
        }

        $config = $config[AdapterInterface::class];
        if (
            ! isset($config['adapters'])
            || ! is_array($config['adapters'])
        ) {
            $this->config = [];
            return $this->config;
        }

        $this->config = $config['adapters'];
        return $this->config;
    }
}
