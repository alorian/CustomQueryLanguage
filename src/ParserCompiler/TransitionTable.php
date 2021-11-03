<?php

namespace App\ParserCompiler;

use App\Lexer\AbstractToken;
use App\Parser\AbstractNode;
use App\ParserCompiler\Operation\OperationAccept;
use App\ParserCompiler\Operation\OperationError;
use App\ParserCompiler\Operation\OperationGoTo;
use App\ParserCompiler\Operation\OperationReduce;
use App\ParserCompiler\Operation\OperationShift;

class TransitionTable
{

    protected array $operationsMap = [];

    // auxiliary table with first terminals foreach non-terminal
    protected array $firstTerminalsTable = [];

    // auxiliary table with following terminals foreach non-terminal
    protected array $followingTerminalsTable = [];

    public function pushTransition(
        State $state,
        string $tokenClass,
        OperationAccept|OperationReduce|OperationShift|OperationGoTo $operation
    ): void {
        if (isset($this->operationsMap[$state->index][$tokenClass])) {
            throw new \RuntimeException('Map is already set for state: "' . $state->index . '", token:"' . $tokenClass . '"');
        }
        $this->operationsMap[$state->index][$tokenClass] = $operation;
    }

    public function getTransition(
        int $stateIndex,
        AbstractToken|AbstractNode $token
    ): OperationAccept|OperationReduce|OperationShift|OperationGoTo|OperationError {
        if (isset($this->operationsMap[$stateIndex][$token::class])) {
            return $this->operationsMap[$stateIndex][$token::class];
        }

        return new OperationError($this->getExpectedTokensClasses($stateIndex));
    }

    public function getExpectedTokensClasses(int $stateIndex): array
    {
        $tokenClassesList = [];
        if (isset($this->operationsMap[$stateIndex])) {
            foreach ($this->operationsMap[$stateIndex] as $tokenClass => $operation) {
                $tokenClassesList[] = $tokenClass;
            }
        }
        return $tokenClassesList;
    }

    public function pushFirstTerminals(string $nonTerminalClass, array $terminalClassesList): void
    {
        $this->firstTerminalsTable[$nonTerminalClass] = $terminalClassesList;
    }

    public function getFirstTerminals(string $nonTerminalClass): array
    {
        return $this->firstTerminalsTable[$nonTerminalClass] ?? [];
    }

    public function pushFollowingTerminals(string $nonTerminalClass, array $terminalClassesList): void
    {
        $this->followingTerminalsTable[$nonTerminalClass] = $terminalClassesList;
    }

    public function getFollowingTerminals(string $nonTerminalClass): array
    {
        return $this->followingTerminalsTable[$nonTerminalClass] ?? [];
    }

}