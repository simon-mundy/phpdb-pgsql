<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Pgsql\Container;

use PhpDb\Adapter\Platform\PlatformInterface;
use PhpDb\Adapter\Pgsql;
use Psr\Container\ContainerInterface;

final class PlatformInterfaceFactory
{
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
        ?array $options = null
    ): PlatformInterface&Pgsql\AdapterPlatform {
        $driver = $options['driver'] ?? null;
        $checkOne = $driver instanceof Pgsql\Driver;
        $checkTwo = $driver instanceof Pgsql\Pdo\Driver;
        if (
            ! ($driver instanceof Pgsql\Driver)
            && ! ($driver instanceof Pgsql\Pdo\Driver)
        ) {
            throw Pgsql\Exception\ContainerException::forServiceFailure(
                PlatformInterface::class,
                'Invalid or missing driver provided'
            );
        }
        return new Pgsql\AdapterPlatform($driver);
    }
}
