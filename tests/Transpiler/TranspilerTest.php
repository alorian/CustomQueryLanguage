<?php

namespace App\Tests\Transpiler;

use App\Exception\TranspilerUnknownFieldException;
use App\Parser\Node\QueryNode;
use App\Transpiler\CustomQueryState;
use App\Transpiler\Transpiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TranspilerTest extends KernelTestCase
{

    public function testSimpleString(): void
    {
        $customQueryState = new CustomQueryState('name = test', 0);

        $transpiler = self::getContainer()->get(Transpiler::class);
        $sqlQueryParts = $transpiler->transpile($customQueryState);

        $this->assertIsString($sqlQueryParts);
        $this->assertNotEmpty($sqlQueryParts);
        $this->assertTrue($customQueryState->isValid());
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
        $customQueryState = new CustomQueryState($wrongQuery, 0);
        $transpiler = self::getContainer()->get(Transpiler::class);
        $transpiler->transpile($customQueryState);
        $this->assertFalse($customQueryState->isValid());
        $this->assertContainsOnlyInstancesOf(TranspilerUnknownFieldException::class, $customQueryState->getErrors());
    }
}
