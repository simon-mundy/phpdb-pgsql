<?php

declare(strict_types=1);

namespace PhpDbIntegrationTest\Pgsql\Extension;

use Exception;
use PhpDbIntegrationTest\Pgsql\FixtureLoader\FixtureLoaderInterface;
use PhpDbIntegrationTest\Pgsql\FixtureLoader\PgsqlFixtureLoader;
use PHPUnit\Event\TestSuite\Started;
use PHPUnit\Event\TestSuite\StartedSubscriber;

use function getenv;
use function printf;

final class IntegrationTestStartedListener implements StartedSubscriber
{
    /** @var FixtureLoaderInterface[] */
    private array $fixtureLoaders = [];

    /**
     * @throws Exception
     */
    public function notify(Started $event): void
    {
        if ($event->testSuite()->name() !== 'integration test') {
            return;
        }

        if ((bool) getenv('TESTS_PHPDB_PGSQL')) {
            $this->fixtureLoaders[] = new PgsqlFixtureLoader();
        }

        if (empty($this->fixtureLoaders)) {
            return;
        }

        printf("\nIntegration test started.\n");

        foreach ($this->fixtureLoaders as $fixtureLoader) {
            $fixtureLoader->createDatabase();
        }
    }
}
