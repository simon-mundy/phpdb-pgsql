<?php

declare(strict_types=1);

namespace PhpDbTest\Pgsql;

use Laminas\ServiceManager\Factory\InvokableFactory;
use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Pgsql\ConfigProvider;
use PhpDb\Adapter\Pgsql\Container\AdapterServiceFactory;
use PhpDb\Adapter\Pgsql\Driver;
use PhpDb\Adapter\Pgsql\Platform;
use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Adapter\Profiler\Profiler;
use PhpDb\Adapter\Profiler\ProfilerInterface;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

#[CoversMethod(ConfigProvider::class, '__invoke')]
#[CoversMethod(ConfigProvider::class, 'getDependencies')]
final class ConfigProviderTest extends TestCase
{
    /** @var array<string, array<array-key, string>> */
    private array $config = [
        'aliases'   => [
            PlatformInterface::class => Platform\Postgresql::class,
            ProfilerInterface::class => Profiler::class,
        ],
        'factories' => [
            AdapterInterface::class    => AdapterServiceFactory::class,
            DriverInterface::class     => Driver\Pdo\DriverFactory::class,
            Platform\Postgresql::class => InvokableFactory::class,
            Profiler::class            => InvokableFactory::class,
        ],
    ];

    public function testProvidesExpectedConfiguration(): ConfigProvider
    {
        $provider = new ConfigProvider();
        self::assertEquals($this->config, $provider->getDependencies());

        return $provider;
    }

    #[Depends('testProvidesExpectedConfiguration')]
    public function testInvocationProvidesDependencyConfiguration(ConfigProvider $provider): void
    {
        self::assertEquals(['dependencies' => $provider->getDependencies()], $provider());
    }
}
