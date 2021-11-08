<?php

namespace App\Tests\ParserCompiler;

use App\Lexer\SimpleToken\EqualToken;
use App\Lexer\SimpleToken\ParenLeftToken;
use App\Lexer\TypeToken\EolToken;
use App\Lexer\TypeToken\StringToken;
use App\Parser\Node\AcceptedQueryNode;
use App\ParserCompiler\FiniteStateMachine;
use App\ParserCompiler\Operation\OperationAccept;
use App\ParserCompiler\Operation\OperationReduce;
use App\ParserCompiler\Operation\OperationShift;
use App\ParserCompiler\Operation\OperationError;
use App\ParserCompiler\ParserCompiler;
use App\ParserCompiler\TransitionTable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ParserCompilerTest extends KernelTestCase
{

    public function testCompilation(): void
    {
        self::bootKernel();
        $parserCompiler = self::getContainer()->get(ParserCompiler::class);
        $parserCompiler->compile();
        $this->assertInstanceOf(TransitionTable::class, $parserCompiler->getTransitionTable());
        $this->assertInstanceOf(FiniteStateMachine::class, $parserCompiler->getFiniteStateMachine());
    }

}
