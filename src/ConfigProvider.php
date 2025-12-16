<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Pgsql;

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
            'aliases'   => [
                PlatformInterface::class => AdapterPlatform::class,
            ],
            'factories' => [
                AdapterInterface::class => Container\AdapterInterfaceFactory::class,
                DriverInterface::class  => Container\PdoDriverInterfaceFactory::class,
                AdapterPlatform::class  => Container\PlatformInterfaceFactory::class,
            ],
        ];
    }
}
