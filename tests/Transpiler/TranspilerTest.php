<?php

namespace App\Tests\Transpiler;

use App\Exception\TranspilerUnknownFieldException;
use App\Parser\Node\QueryNode;
use App\Transpiler\CustomQueryState;
use App\Transpiler\Transpiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TranspilerTest extends KernelTestCase
{
    protected static ?Transpiler $transpiler;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::bootKernel();
        static::$transpiler = self::getContainer()->get(Transpiler::class);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        static::$transpiler = null;
    }

    public function testSimpleString(): void
    {
        $rawQueryState = new CustomQueryState('name = test', 0);
        $sqlQueryParts = static::$transpiler->transpile($rawQueryState);

        $this->assertIsString($sqlQueryParts);
        $this->assertNotEmpty($sqlQueryParts);
        $this->assertTrue($rawQueryState->isValid());
    }

    public function wrongQueryProvider(): array
    {
        return [
            ['gwgwegewg = wegwegwege'],
        ];
    }

    /**
     * @dataProvider wrongQueryProvider
     */
    public function testTranspilerUnknownFieldException(string $wrongQuery): void
    {
        $rawQueryState = new CustomQueryState($wrongQuery, 0);
        static::$transpiler->transpile($rawQueryState);
        $this->assertFalse($rawQueryState->isValid());
        $this->assertContainsOnlyInstancesOf(TranspilerUnknownFieldException::class, $rawQueryState->getErrors());
    }
}
