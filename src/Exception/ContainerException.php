<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Pgsql\Exception;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;

final class ContainerException extends RuntimeException implements ContainerExceptionInterface
{
    public static function forServiceFailure(
        string $serviceName,
        string $reason
    ): self {
        return new self(
            sprintf(
                'Failed to create service "%s": %s',
                 $serviceName,
                 $reason
            ),
            0,
        );
    }
}
