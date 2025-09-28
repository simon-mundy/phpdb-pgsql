<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Pgsql;

use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\ResultSet\ResultSetInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class AdapterServiceFactory implements FactoryInterface
{
    /**
     * Create db adapter service
     *
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @return Adapter
     */
    public function __invoke(ContainerInterface $container, string $requestedName, ?array $options = null): Adapter
    {
        $resultSetPrototype = $container->has(ResultSetInterface::class) ? $container->get(ResultSetInterface::class) : null;
        $profiler           = $this->createProfiler($container, $config['db']['profiler'] ?? []);

        return new Adapter(
            $container->get(DriverInterface::class),
            $container->get(Platform\Postgresql::class),
            $resultSetPrototype,
            $profiler
        );
    }
}