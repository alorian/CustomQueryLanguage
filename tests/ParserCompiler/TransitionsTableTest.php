<?php

namespace App\Tests\ParserCompiler;

use App\Lexer\SimpleToken\EqualToken;
use App\ParserCompiler\Operation\OperationAccept;
use App\ParserCompiler\Operation\OperationError;
use App\ParserCompiler\Operation\OperationGoTo;
use App\ParserCompiler\Operation\OperationReduce;
use App\ParserCompiler\Operation\OperationShift;
use App\ParserCompiler\State;
use App\ParserCompiler\TransitionTable;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TransitionsTableTest extends KernelTestCase
{

    #[ArrayShape([
        'stateIndex' => 'int|null',
        'symbolClass' => 'string|null'
    ])]
    protected function findOperation(TransitionTable $transitionsTable, string $operationClass): array
    {
        $reflection = new \ReflectionClass($transitionsTable);
        $reflectionProperty = $reflection->getProperty('operationsMap');
        $reflectionProperty->setAccessible('operationsMap');
        $operationsMap = $reflectionProperty->getValue($transitionsTable);

        $operationStateIndex = null;
        $symbolClass = null;
        foreach ($operationsMap as $stateIndex => $symbolsList) {
            foreach ($symbolsList as $tokenClass => $operation) {
                if ($operation instanceof $operationClass) {
                    $operationStateIndex = $stateIndex;
                    $symbolClass = $tokenClass;
                    break 2;
                }
            }
        }

        return [
            'stateIndex' => $operationStateIndex,
            'symbolClass' => $symbolClass
        ];
    }

    public function testHasAcceptOperation(): void
    {
        self::bootKernel();
        $transitionsTable = self::getContainer()->get(TransitionTable::class);

        $operationCoordinates = $this->findOperation($transitionsTable, OperationAccept::class);

        $this->assertNotNull($operationCoordinates['stateIndex']);
        $this->assertNotNull($operationCoordinates['symbolClass']);

        $node = new $operationCoordinates['symbolClass'];
        $operation = $transitionsTable->getTransition($operationCoordinates['stateIndex'], $node);
        $this->assertInstanceOf(OperationAccept::class, $operation);
    }

    public function testHasShiftOperation(): void
    {
        self::bootKernel();
        $transitionsTable = self::getContainer()->get(TransitionTable::class);

        $operationCoordinates = $this->findOperation($transitionsTable, OperationShift::class);

        $this->assertNotNull($operationCoordinates['stateIndex']);
        $this->assertNotNull($operationCoordinates['symbolClass']);

        $node = new $operationCoordinates['symbolClass'];
        $operation = $transitionsTable->getTransition($operationCoordinates['stateIndex'], $node);
        $this->assertInstanceOf(OperationShift::class, $operation);
    }

    public function testHasReduceOperation(): void
    {
        self::bootKernel();
        $transitionsTable = self::getContainer()->get(TransitionTable::class);

        $operationCoordinates = $this->findOperation($transitionsTable, OperationReduce::class);

        $this->assertNotNull($operationCoordinates['stateIndex']);
        $this->assertNotNull($operationCoordinates['symbolClass']);

        $node = new $operationCoordinates['symbolClass'];
        $operation = $transitionsTable->getTransition($operationCoordinates['stateIndex'], $node);
        $this->assertInstanceOf(OperationReduce::class, $operation);
    }

    public function testHasGoToOperation(): void
    {
        self::bootKernel();
        $transitionsTable = self::getContainer()->get(TransitionTable::class);

        $operationCoordinates = $this->findOperation($transitionsTable, OperationGoTo::class);

        $this->assertNotNull($operationCoordinates['stateIndex']);
        $this->assertNotNull($operationCoordinates['symbolClass']);

        $node = new $operationCoordinates['symbolClass'];
        $operation = $transitionsTable->getTransition($operationCoordinates['stateIndex'], $node);
        $this->assertInstanceOf(OperationGoTo::class, $operation);
    }

    public function testOperationError(): void
    {
        self::bootKernel();
        $transitionsTable = self::getContainer()->get(TransitionTable::class);
        $operation = $transitionsTable->getTransition(0, new EqualToken());
        $this->assertInstanceOf(OperationError::class, $operation);
    }

    public function testMultipleOperationsInSameCell(): void
    {
        self::bootKernel();
        $transitionsTable = self::getContainer()->get(TransitionTable::class);

        $operationCoordinates = $this->findOperation($transitionsTable, OperationShift::class);

        $this->assertNotNull($operationCoordinates['stateIndex']);
        $this->assertNotNull($operationCoordinates['symbolClass']);

        $this->expectException(\RuntimeException::class);
        $state = new State([], $operationCoordinates['stateIndex']);
        $transitionsTable->pushTransition($state, $operationCoordinates['symbolClass'], new OperationAccept());
    }
}
