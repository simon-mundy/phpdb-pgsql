<?php

declare(strict_types=1);

namespace PhpDbTest\Adapter\Pgsql\Adapter;

use PhpDb\Adapter\AdapterInterface;
use PhpDbTestAsset\Pgsql\SetupTrait;
use PHPUnit\Framework\TestCase;

final class SetupTest extends TestCase
{
    use SetupTrait;

    public function testSetUp(): void
    {
        $adapter = $this->getAdapter();
        self::assertInstanceOf(AdapterInterface::class, $adapter);
    }
}
