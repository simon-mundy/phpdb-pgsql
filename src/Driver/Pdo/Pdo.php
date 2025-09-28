<?php

namespace PhpDb\Adapter\Pgsql\Driver\Pdo;

use PhpDb\Adapter\Driver\ConnectionInterface;
use PhpDb\Adapter\Driver\DriverInterface;
use PhpDb\Adapter\Driver\Feature\DriverFeatureInterface;
use PhpDb\Adapter\Driver\Pdo\AbstractPdo;
use PhpDb\Adapter\Driver\Pdo\Statement;
use PhpDb\Adapter\Driver\ResultInterface;
use PhpDb\Adapter\Driver\PdoStatementInterface;
use PhpDb\Adapter\Profiler;
use PhpDb\Adapter\Pgsql\Driver\DatabasePlatformNameTrait;
use Override;
use PDO as PDOConnection;

class Pdo extends AbstractPdo implements DriverInterface, DriverFeatureInterface, Profiler\ProfilerAwareInterface
{
    use DatabasePlatformNameTrait;

    public function __construct(
        ConnectionInterface|PDOConnection|array $connection,
        ?Statement $statementPrototype = null,
        ?Result $resultPrototype = null,
        $features = self::FEATURES_DEFAULT
    ) {
        if (! $connection instanceof ConnectionInterface) {
            $connection = new Connection($connection);
        }

        parent::__construct($connection, $statementPrototype, $resultPrototype, $features);
    }

    /**
     * Register statement prototype
     */
    public function registerStatementPrototype(PdoStatementInterface $statementPrototype): void
    {
        $this->statementPrototype = $statementPrototype->setDriver($this);
    }

    /**
     * Register result prototype
     */
    public function registerResultPrototype(ResultInterface $resultPrototype): void
    {
        $this->resultPrototype = $resultPrototype;
    }

    /**
     * Setup the default features for Pdo
     *
     * @return $this Provides a fluent interface
     */
    public function setupDefaultFeatures(): static
    {
        return $this;
    }

    /**
     * Register connection
     *
     * @return $this Provides a fluent interface
     */
    public function registerConnection(PDOConnection|ConnectionInterface $connection): static
    {
        $this->connection = $connection->setDriver($this);

        return $this;
    }

    #[Override]
    /**
     * @param resource $resource
     * @param mixed    $context
     * @return \PhpDb\Adapter\Driver\Pdo\Result
     */
    public function createResult($resource, $context = null): Result
    {
        $result           = clone $this->resultPrototype;
        $result->initialize($resource, $this->connection->getLastGeneratedValue());

        return $result;
    }
}
