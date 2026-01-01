<?php

declare(strict_types=1);

namespace PhpDbIntegrationTest\Adapter\Pgsql;

use Laminas\Stdlib\ErrorHandler;
use PhpDb\Adapter\Pgsql\AdapterPlatform;
use PhpDbTestAsset\Pgsql\SetupTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;

use const E_USER_NOTICE;

#[CoversClass(AdapterPlatform::class)]
#[CoversMethod(AdapterPlatform::class, 'quoteValue')]
final class AdapterPlatformTest extends TestCase
{
    use SetupTrait;

    /**
     * @return void
     */
    public function testQuoteValueWithPgsql(): void
    {
        $adapter = $this->getAdapter(self::NATIVE_ADAPTER);
        $pgsql   = $adapter->getPlatform();
        ErrorHandler::start(E_USER_NOTICE);
        $value   = $pgsql->quoteValue('value');
        ErrorHandler::stop();
        self::assertEquals('\'value\'', $value);
        // Should this assertion be?
        //todo:  self::assertEquals('E\'value\'', $value);
    }

    /**
     * @return void
     */
    public function testQuoteValueWithPdoPgsql(): void
    {
        $adapter = $this->getAdapter(self::PDO_ADAPTER);
        $pgsql   = $adapter->getPlatform();
        ErrorHandler::start(E_USER_NOTICE);
        $value = $pgsql->quoteValue('value');
        ErrorHandler::stop();
        self::assertEquals('\'value\'', $value);
    }
}
