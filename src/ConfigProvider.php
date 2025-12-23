<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Pgsql;

use Laminas\ServiceManager\Factory\InvokableFactory;
use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\Driver\ConnectionInterface;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Driver\PdoConnectionInterface;
use PhpDb\Adapter\Driver\PdoDriverInterface;
use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Adapter\Pgsql\Pdo\Connection as PdoConnection;
use PhpDb\Adapter\Pgsql\Pdo\Driver as PdoDriver;
use PhpDb\Adapter\Profiler\Profiler;
use PhpDb\Adapter\Profiler\ProfilerInterface;
use PhpDb\Container\AdapterAbstractServiceFactory;
use PhpDb\Metadata\MetadataInterface;
use PhpDb\ResultSet\ResultSetInterface;

final readonly class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies'          => $this->getDependencies(),
            AdapterInterface::class => $this->getAdapters(),
        ];
    }

    public function getAdapters(): array
    {
        return [
            'adapters' => [
                AdapterInterface::class => [
                    'driver' => Driver::class,
                    'connection' => [
                        'host'     => 'localhost',
                        'port'     => 5432,
                        'username' => 'your_username',
                        'password' => 'your_password',
                        'database' => 'your_database',
                    ],
                ],
            ],
        ];
    }

    public function getDependencies(): array
    {
        return [
            'abstract_factories' => [
                Container\AdapterAbstractServiceFactory::class,
            ],
            'aliases'   => [
                DriverInterface::class        => Driver::class,
                'pgsql'                       => Driver::class,
                'PgSQL'                       => Driver::class,
                'Postgresql'                  => Driver::class,
                'PostgreSQL'                  => Driver::class,
                PdoDriverInterface::class     => PdoDriver::class,
                'pdo_pgsql'                   => PdoDriver::class,
                'PDO_pgsql'                   => PdoDriver::class,
                'PDO_PgSQL'                   => PdoDriver::class,
                'Pdo_PgSQL'                   => PdoDriver::class,
                'PDO_postgresql'              => PdoDriver::class,
                'pdo_postgresql'              => PdoDriver::class,
                'PDO_Postgresql'              => PdoDriver::class,
                'Pdo_Postgresql'              => PdoDriver::class,
                'PDO_PostgreSQL'              => PdoDriver::class,
                'pdo_PostgreSQL'              => PdoDriver::class,
                ConnectionInterface::class    => Connection::class,
                MetadataInterface::class      => Metadata\Source::class,
                PdoConnectionInterface::class => PdoConnection::class,
                PlatformInterface::class      => AdapterPlatform::class,
            ],
            'factories' => [
                //AdapterInterface::class    => Container\AdapterInterfaceFactory::class,
                AdapterPlatform::class     => Container\PlatformInterfaceFactory::class,
                Connection::class          => Container\ConnectionInterfaceFactory::class,
                Metadata\Source::class     => Container\MetadataInterfaceFactory::class,
                Statement::class           => Container\StatementInterfaceFactory::class,
                Driver::class              => Container\DriverInterfaceFactory::class,
                PdoConnection::class       => Container\PdoConnectionInterfaceFactory::class,
                PdoDriver::class           => Container\PdoDriverInterfaceFactory::class,
                // Provide the following if you wish to override the Profiler implementation
                //ProfilerInterface::class => YourCustomProfilerFactory::class,
                // Provide the following if you wish to override the ResultSet implementation
                //ResultSetInterface::class => YourCustomResultSetFactory::class,
            ],
        ];
    }
}
