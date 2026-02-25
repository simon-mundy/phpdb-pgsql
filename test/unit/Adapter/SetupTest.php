<?php

declare(strict_types=1);

namespace PhpDbTest\Pgsql;

use PhpDb\Adapter\AdapterInterface;
use PhpDbTestAsset\Pgsql\SetupTrait;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;

#[CoversTrait(SetupTrait::class)]
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
        $adapter = $this->getAdapter(self::NATIVE_ADAPTER);
        self::assertInstanceOf(AdapterInterface::class, $adapter);
    }
}
