<?php

namespace App\Tests\Transpiler;

use App\Exception\TranspilerUnknownFieldException;
use App\Transpiler\CustomQueryState;
use App\Transpiler\Transpiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TranspilerTest extends KernelTestCase
{

    public function testTranspilerUnknownFieldException(): void
    {
        $wrongQuery = 'gwgwegewg = wegwegwege';
        $customQueryState = new CustomQueryState($wrongQuery, 0);
        $transpiler = self::getContainer()->get(Transpiler::class);
        $transpiler->transpile($customQueryState);
        $this->assertFalse($customQueryState->isValid());
        $this->assertContainsOnlyInstancesOf(TranspilerUnknownFieldException::class, $customQueryState->getErrors());
    }


    public function AlphanumericComparisonExpressionNode(): void
    {
        $customQueryState = new CustomQueryState('name = test', 0);

        $transpiler = self::getContainer()->get(Transpiler::class);
        $sqlQueryParts = $transpiler->transpile($customQueryState);

        $this->assertIsString($sqlQueryParts);
        $this->assertNotEmpty($sqlQueryParts);
        $this->assertTrue($customQueryState->isValid());
    }


}
