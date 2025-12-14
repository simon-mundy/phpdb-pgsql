<?php

declare(strict_types=1);

namespace PhpDb\Adapter\Pgsql\Driver\Pgsql;

use PgSql\Result as PgSqlResult;
use PhpDb\Adapter\Driver\ResultInterface;
use PhpDb\Adapter\Exception;
use ReturnTypeWillChange;

use function pg_affected_rows;
use function pg_fetch_assoc;
use function pg_num_fields;
use function pg_num_rows;

// phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
class Result implements ResultInterface
{
    /** @var PgSqlResult */
    protected $resource;

    protected int $position = 0;

    protected int $count = 0;

    protected mixed $generatedValue;

    /**
     * Initialize
     *
     * @param resource $resource
     * @param int|string $generatedValue
     * @return void
     * @throws Exception\InvalidArgumentException
     */
    public function initialize(PgSqlResult $resource, $generatedValue)
    {
        // if (
        //     ! $resource instanceof PgSqlResult
        //     && (
        //         ! is_resource($resource)
        //         || 'pgsql result' !== get_resource_type($resource)
        //     )
        // ) {
        //     throw new Exception\InvalidArgumentException('Resource not of the correct type.');
        // }

        $this->resource       = $resource;
        $this->count          = pg_num_rows($this->resource);
        $this->generatedValue = $generatedValue;
    }

    /**
     * Current
     *
     * @return array|bool|mixed
     */
    #[ReturnTypeWillChange]
    public function current()
    {
        if ($this->count === 0) {
            return false;
        }
        return pg_fetch_assoc($this->resource, $this->position);
    }

    /**
     * Next
     *
     * @return void
     */
    #[ReturnTypeWillChange]
    public function next()
    {
        $this->position++;
    }

    /**
     * Key
     *
     * @return int|mixed
     */
    #[ReturnTypeWillChange]
    public function key()
    {
        return $this->position;
    }

    /**
     * Valid
     *
     * @return bool
     */
    #[ReturnTypeWillChange]
    public function valid()
    {
        return $this->position < $this->count;
    }

    /**
     * Rewind
     *
     * @return void
     */
    #[ReturnTypeWillChange]
    public function rewind()
    {
        $this->position = 0;
    }

    /** todo: track this */
    public function buffer(): void
    {
    }

    /**
     * Is buffered
     */
    public function isBuffered(): ?bool
    {
        return false;
    }

    public function isQueryResult(): bool
    {
        return pg_num_fields($this->resource) > 0;
    }

    /**
     * Get affected rows
     */
    public function getAffectedRows(): int
    {
        return pg_affected_rows($this->resource);
    }

    /**
     * Get generated value
     *
     * @return mixed|null
     */
    public function getGeneratedValue()
    {
        return $this->generatedValue;
    }

    /**
     * Get resource
     */
    public function getResource()
    {
        // TODO: Implement getResource() method.
    }

    /**
     * Count
     *
     * @return int The custom count as an integer.
     */
    #[ReturnTypeWillChange]
    public function count()
    {
        return $this->count;
    }

    /**
     * Get field count
     */
    public function getFieldCount(): int
    {
        return pg_num_fields($this->resource);
    }
}
