<?php

declare(strict_types=1);

namespace PhpDbIntegrationTest\Adapter\Pgsql\FixtureLoader;

interface FixtureLoaderInterface
{
    public function createDatabase(): void;

    public function dropDatabase(): void;
}
