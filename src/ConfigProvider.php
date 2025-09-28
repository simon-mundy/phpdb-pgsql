<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Pgsql;

use Laminas\ServiceManager\Factory\InvokableFactory;
use PhpDb\Adapter\AdapterInterface;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Platform\PlatformInterface;

readonly class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'aliases' => [
                PlatformInterface::class => Platform\Postgresql::class,
            ],
            'factories' => [
                AdapterInterface::class    => AdapterServiceFactory::class,
                DriverInterface::class     => Driver\Pdo\DriverFactory::class,
                Platform\Postgresql::class => InvokableFactory::class,
            ],
        ];
    }
}