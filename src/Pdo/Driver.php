<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Pgsql\Driver\Pdo;

use Override;
use PDOStatement;
use PhpDb\Adapter\Driver\Pdo\AbstractPdo;
use PhpDb\Adapter\Driver\Pdo\Result;
use PhpDb\Adapter\Driver\ResultInterface;
use PhpDb\Adapter\Pgsql\Driver\DatabasePlatformNameTrait;

class Driver extends AbstractPdo
{
    use DatabasePlatformNameTrait;

    /**
     * @param PDOStatement $resource
     */
    #[Override]
    public function createResult($resource): ResultInterface
    {
        /** @var Result $result */
        $result = clone $this->resultPrototype;
        $result->initialize($resource, $this->connection->getLastGeneratedValue());

        return $result;
    }
}
