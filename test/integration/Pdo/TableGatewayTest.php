<?php

declare(strict_types=1);

namespace PhpDbIntegrationTest\Adapter\Pgsql\Driver\Pdo;

use PhpDb\Sql\TableIdentifier;
use PhpDb\TableGateway\Feature\FeatureSet;
use PhpDb\TableGateway\Feature\SequenceFeature;
use PhpDb\TableGateway\TableGateway;
use PhpDbTestAsset\Pgsql\SetupTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TableGateway::class)]
final class TableGatewayTest extends TestCase
{
    use SetupTrait;

    public function testLastInsertValue(): void
    {
        $table      = new TableIdentifier('test_seq');
        $featureSet = new FeatureSet();
        $featureSet->addFeature(new SequenceFeature('id', 'test_seq_id_seq'));

        $tableGateway = new TableGateway($table, $this->getAdapter(self::PDO_ADAPTER), $featureSet);

        $tableGateway->insert(['foo' => 'bar']);
        self::assertSame(1, $tableGateway->getLastInsertValue());

        $tableGateway->insert(['foo' => 'baz']);
        self::assertSame(2, $tableGateway->getLastInsertValue());
    }
}
