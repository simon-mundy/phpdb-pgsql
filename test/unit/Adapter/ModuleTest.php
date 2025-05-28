<?php

namespace LaminasTest\Db\Pgsql;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Driver\DriverInterface;
use Laminas\Db\Adapter\Platform\PlatformInterface;
use Laminas\Db\Adapter\Profiler\Profiler;
use Laminas\Db\Adapter\Profiler\ProfilerInterface;
use Laminas\Db\Pgsql\AdapterServiceFactory;
use Laminas\Db\Pgsql\Driver;
use Laminas\Db\Pgsql\Module;
use Laminas\Db\Pgsql\Platform;
use Laminas\ServiceManager\Factory\InvokableFactory;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

#[CoversMethod(Module::class, 'getConfig')]
final class ModuleTest extends TestCase
{
    /** @var array<string, array<array-key, string>> */
    private array $config = [
        'aliases'   => [
            PlatformInterface::class => Platform\Pgsql::class,
            ProfilerInterface::class => Profiler::class,
        ],
        'factories' => [
            AdapterInterface::class => AdapterServiceFactory::class,
            DriverInterface::class  => Driver\Pdo\DriverFactory::class,
            Platform\Pgsql::class  => InvokableFactory::class,
            Profiler::class         => InvokableFactory::class,
        ],
    ];

    public function testProvidesExpectedConfiguration(): Module
    {
        $provider = new Module();
        self::assertEquals(['service_manager' => $this->config], $provider->getConfig());

        return $provider;
    }
}
