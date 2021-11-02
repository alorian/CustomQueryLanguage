<?php

namespace App\Tests\ParserCompiler;

use App\Lexer\SimpleToken\EqualToken;
use App\Lexer\SimpleToken\ParenLeftToken;
use App\Lexer\TypeToken\EolToken;
use App\Lexer\TypeToken\StringToken;
use App\Parser\Node\AcceptedQueryNode;
use App\ParserCompiler\Operation\OperationAccept;
use App\ParserCompiler\Operation\OperationReduce;
use App\ParserCompiler\Operation\OperationShift;
use App\ParserCompiler\Operation\OperationError;
use App\ParserCompiler\ParserCompiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ParserCompilerTest extends KernelTestCase
{
    protected static ?ParserCompiler $parserCompiler;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::bootKernel();
        static::$parserCompiler = self::getContainer()->get(ParserCompiler::class);
        static::$parserCompiler->compile();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        static::$parserCompiler = null;
    }

    public function testErrorOperation(): void
    {
        $transitionsTable = static::$parserCompiler->getTransitionTable();

        $equalToken = new EqualToken();
        $operation = $transitionsTable->getTransition(0, $equalToken);
        $this->assertInstanceOf(OperationError::class, $operation);
    }

    public function testShiftOperation(): void
    {
        $transitionsTable = static::$parserCompiler->getTransitionTable();

        $parenLeftToken = new ParenLeftToken();
        $operation = $transitionsTable->getTransition(0, $parenLeftToken);
        $this->assertInstanceOf(OperationShift::class, $operation);

        $stringToken = new StringToken();
        $operation = $transitionsTable->getTransition(0, $stringToken);
        $this->assertInstanceOf(OperationShift::class, $operation);
    }

    public function testReduceOperation(): void
    {
        $transitionsTable = static::$parserCompiler->getTransitionTable();

        $finiteStateMachine = static::$parserCompiler->getFiniteStateMachine();

        $statesList = $finiteStateMachine->getStatesList();
        $anyReduceStateIndex = null;
        $reduceFinalToken = null;
        foreach ($statesList as $state) {
            foreach ($state->conditions as $stateCondition) {
                if ($stateCondition->marked() === null) {
                    $anyReduceStateIndex = $state->index;
                    $finalTokenClass = $stateCondition->getPrevClassName();
                    $reduceFinalToken = new $finalTokenClass();
                    break;
                }
            }
        }

        $this->assertNotNull($anyReduceStateIndex);
        $this->assertNotNull($reduceFinalToken);

        $operation = $transitionsTable->getTransition($anyReduceStateIndex, $reduceFinalToken);
        $this->assertInstanceOf(OperationReduce::class, $operation);
    }

    public function testAcceptOperation(): void
    {
        $transitionsTable = static::$parserCompiler->getTransitionTable();
        $finiteStateMachine = static::$parserCompiler->getFiniteStateMachine();

        $statesList = $finiteStateMachine->getStatesList();
        $acceptStateIndex = null;
        foreach ($statesList as $state) {
            foreach ($state->conditions as $stateCondition) {
                if ($stateCondition->rule->left === AcceptedQueryNode::class) {
                    $acceptStateIndex = $state->index;
                    break;
                }
            }
        }

        $this->assertNotNull($acceptStateIndex);

        $eolToken = new EolToken();
        $operation = $transitionsTable->getTransition($acceptStateIndex, $eolToken);
        $this->assertInstanceOf(OperationAccept::class, $operation);
    }
}
