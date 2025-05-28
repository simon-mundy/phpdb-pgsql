<?php

declare(strict_types=1);

namespace Laminas\Db\Pgsql;

use Laminas\Db\Adapter\AbstractAdapter;
use Laminas\Db\Pgsql\Driver\Pdo\Pdo as PgsqlDriver;

/**
 * @property PgsqlDriver   $driver
 */
class Adapter extends AbstractAdapter
{
    /** @var PgsqlDriver */
    protected $driver;
}
