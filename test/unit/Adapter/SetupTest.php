<?php

declare(strict_types=1);

namespace PhpDbTest\Adapter\Pgsql\Adapter;

use PhpDb\Adapter\AdapterInterface;
use PhpDbTestAsset\Pgsql\SetupTrait;
use PHPUnit\Framework\TestCase;

final class SetupTest extends TestCase
{
    use SetupTrait;

    public function testAdapterAbstractFactoryBuildsNativeDriver(): void
    {
        $adapter = $this->getAdapter();
        self::assertInstanceOf(AdapterInterface::class, $adapter);
    }

    public function testAdapterInterfaceFactoryBuildsNativeDriver(): void
    {
        $adapter = $this->getAdapter(AdapterInterface::class);
        self::assertInstanceOf(AdapterInterface::class, $adapter);
    }
}
