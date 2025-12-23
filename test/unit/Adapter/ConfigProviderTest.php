<?php

declare(strict_types=1);

namespace PhpDbTest\Pgsql;

use Laminas\ServiceManager\Factory\InvokableFactory;
use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\Driver\ConnectionInterface;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Driver\PdoConnectionInterface;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Pgsql;
use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Adapter\Profiler\Profiler;
use PhpDb\Adapter\Profiler\ProfilerInterface;
use PhpDb\Container\AdapterAbstractServiceFactory;
use PhpDb\Metadata\MetadataInterface;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

#[CoversMethod(Pgsql\ConfigProvider::class, '__invoke')]
#[CoversMethod(Pgsql\ConfigProvider::class, 'getDependencies')]
final class ConfigProviderTest extends TestCase
{
    /** @var array<string, array<array-key, string>> */
    private array $config = [
        'aliases'   => [
            DriverInterface::class        => Pgsql\Driver::class,
            'pgsql'                       => Pgsql\Driver::class,
            'PgSQL'                       => Pgsql\Driver::class,
            'Postgresql'                  => Pgsql\Driver::class,
            'PostgreSQL'                  => Pgsql\Driver::class,
            PdoDriverInterface::class     => Pgsql\Pdo\Driver::class,
            'pdo_pgsql'                   => Pgsql\Pdo\Driver::class,
            'PDO_pgsql'                   => Pgsql\Pdo\Driver::class,
            'PDO_PgSQL'                   => Pgsql\Pdo\Driver::class,
            'Pdo_PgSQL'                   => Pgsql\Pdo\Driver::class,
            'PDO_postgresql'              => Pgsql\Pdo\Driver::class,
            'pdo_postgresql'              => Pgsql\Pdo\Driver::class,
            'PDO_Postgresql'              => Pgsql\Pdo\Driver::class,
            'Pdo_Postgresql'              => Pgsql\Pdo\Driver::class,
            'PDO_PostgreSQL'              => Pgsql\Pdo\Driver::class,
            'pdo_PostgreSQL'              => Pgsql\Pdo\Driver::class,
            ConnectionInterface::class    => Pgsql\Connection::class,
            MetadataInterface::class      => Pgsql\Metadata\Source::class,
            PdoConnectionInterface::class => Pgsql\Pdo\Connection::class,
            PlatformInterface::class      => Pgsql\AdapterPlatform::class,
            ProfilerInterface::class      => Profiler::class,
        ],
        'factories' => [
            AdapterInterface::class      => Pgsql\Container\AdapterInterfaceFactory::class,
            Pgsql\Driver::class          => Pgsql\Container\DriverInterfaceFactory::class,
            Pgsql\Pdo\Driver::class      => Pgsql\Container\PdoDriverInterfaceFactory::class,
            Pgsql\Connection::class      => Pgsql\Container\ConnectionInterfaceFactory::class,
            Pgsql\Pdo\Connection::class  => Pgsql\Container\PdoConnectionInterfaceFactory::class,
            Pgsql\AdapterPlatform::class => Pgsql\Container\PlatformInterfaceFactory::class,
            Profiler::class              => InvokableFactory::class,
        ],
    ];

    public function testProvidesExpectedConfiguration(): Pgsql\ConfigProvider
    {
        $provider = new Pgsql\ConfigProvider();
        self::assertEquals($this->config, $provider->getDependencies());

        return $provider;
    }

    #[Depends('testProvidesExpectedConfiguration')]
    public function testInvocationProvidesDependencyConfiguration(Pgsql\ConfigProvider $provider): void
    {
        self::assertEquals(['dependencies' => $provider->getDependencies()], $provider());
    }
}
