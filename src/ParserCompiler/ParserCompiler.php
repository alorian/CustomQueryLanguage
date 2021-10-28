<?php

namespace App\ParserCompiler;


use App\Lexer\AbstractToken;
use App\Lexer\TypeToken\EolToken;
use App\Parser\AbstractNode;
use App\Parser\Node\AcceptedQueryNode;
use App\Parser\Node\ComparisonExpressionNode;
use App\Parser\Node\ComparisonOperatorNode;
use App\Parser\Node\ConditionalExpressionNode;
use App\Parser\Node\ConditionalFactorNode;
use App\Parser\Node\ConditionalPrimaryNode;
use App\Parser\Node\ConditionalTermNode;
use App\Parser\Node\ContainsExpressionNode;
use App\Parser\Node\ContainsOperatorNode;
use App\Parser\Node\FieldNode;
use App\Parser\Node\PrimaryNode;
use App\Parser\Node\QueryNode;
use App\Parser\Node\SimpleCondExpressionNode;
use App\ParserCompiler\Operation\OperationAccept;
use App\ParserCompiler\Operation\OperationGoTo;
use App\ParserCompiler\Operation\OperationReduce;
use App\ParserCompiler\Operation\OperationShift;

class ParserCompiler
{
    protected array $nonTerminalsList = [
        AcceptedQueryNode::class,
        QueryNode::class,
        ConditionalExpressionNode::class,
        ConditionalTermNode::class,
        ConditionalFactorNode::class,
        ConditionalPrimaryNode::class,
        SimpleCondExpressionNode::class,
        ComparisonExpressionNode::class,
        ComparisonOperatorNode::class,
        ContainsExpressionNode::class,
        ContainsOperatorNode::class,
        PrimaryNode::class,
        FieldNode::class,
    ];

    /** @var array|GrammarRule[] */
    protected array $grammarRulesList = [];

    protected array $grammarRulesMap = [];

    protected array $firstTerminalsTable = [];

    protected array $followingTerminalsTable = [];

    public function __construct(
        protected FiniteStateMachine $finiteStateMachine,
        protected TransitionTable $transitionTable,
    ) {
        // making rules collection
        $grammarRuleIndex = 0;
        foreach ($this->nonTerminalsList as $nonTerminalClassName) {
            foreach ($nonTerminalClassName::RULES as $RULE_ITEMS) {
                $grammarRule = new GrammarRule($nonTerminalClassName, $RULE_ITEMS, $grammarRuleIndex);
                $this->grammarRulesList[] = $grammarRule;
                $this->grammarRulesMap[$nonTerminalClassName][] = $grammarRule;
                $grammarRuleIndex++;
            }
        }
        $this->finiteStateMachine->setGrammarRulesList($this->grammarRulesList);

        // building auxiliary table with first terminals foreach non-terminal
        foreach ($this->nonTerminalsList as $nonTerminalClassName) {
            $this->firstTerminalsTable[$nonTerminalClassName] = $this->findFirstTerminals($nonTerminalClassName);
        }

        // building auxiliary table with following terminals foreach non-terminal
        foreach ($this->nonTerminalsList as $nonTerminalClassName) {
            $this->followingTerminalsTable[$nonTerminalClassName] = $this->findFollowingTerminals($nonTerminalClassName);
        }
    }

    public function compile(): void
    {
        $this->finiteStateMachine->build();

        $statesList = $this->finiteStateMachine->getStatesList();
        $transitionsList = $this->finiteStateMachine->getTransitionsList();

        foreach ($statesList as $state) {
            /** @var State $state */
            $this->addReduceOperations($state);

            if (isset($transitionsList[$state->index])) {
                foreach ($transitionsList[$state->index] as $symbol => $followingState) {
                    if (is_subclass_of($symbol, AbstractNode::class)) {
                        $this->transitionTable->pushTransition(
                            $state,
                            $symbol,
                            new OperationGoTo($state, $followingState)
                        );
                    } else {
                        $this->transitionTable->pushTransition(
                            $state,
                            $symbol,
                            new OperationShift($state, $followingState)
                        );
                    }
                }
            }
        }
    }

    public function getTransitionTable(): TransitionTable
    {
        return $this->transitionTable;
    }

    public function getFiniteStateMachine(): FiniteStateMachine
    {
        return $this->finiteStateMachine;
    }

    protected function addReduceOperations(State $state): void
    {
        foreach ($state->conditions as $stateCondition) {
            if ($stateCondition->marked() === null) {
                if ($stateCondition->rule->left === AcceptedQueryNode::class) {
                    $this->transitionTable->pushTransition(
                        $state,
                        EolToken::class,
                        new OperationAccept()
                    );
                } else {
                    foreach ($this->followingTerminalsTable[$stateCondition->rule->left] as $terminalClass) {
                        $this->transitionTable->pushTransition(
                            $state,
                            $terminalClass,
                            new OperationReduce($stateCondition->rule)
                        );
                    }
                }
            }
        }
    }

    protected function findFollowingTerminals(string $nonTerminalClassName): array
    {
        $followingTerminalsList = [];

        foreach ($this->grammarRulesList as $rule) {
            if ($rule->last() === $nonTerminalClassName) {
                foreach ($this->findFollowingTerminals($rule->left) as $terminalClass) {
                    $followingTerminalsList[$terminalClass] = $terminalClass;
                }
            } elseif ($rule->hasItem($nonTerminalClassName)) {
                $followingItem = $rule->nextItemAfter($nonTerminalClassName);
                if (is_subclass_of($followingItem, AbstractToken::class)) {
                    $followingTerminalsList[$followingItem] = $followingItem;
                } else {
                    foreach ($this->firstTerminalsTable[$followingItem] as $terminalClass) {
                        $followingTerminalsList[$terminalClass] = $terminalClass;
                    }
                }
            }
        }

        return $followingTerminalsList;
    }

    protected function findFirstTerminals(string $nonTerminalClassName): array
    {
        $firstTerminalsList = [];

        foreach ($this->grammarRulesMap[$nonTerminalClassName] as $rule) {
            /** @var GrammarRule $rule */
            $firstSymbol = $rule->first();
            if (is_subclass_of($firstSymbol, AbstractToken::class)) {
                $firstTerminalsList[$firstSymbol] = $firstSymbol;
            } elseif ($firstSymbol !== $nonTerminalClassName) {
                foreach ($this->findFirstTerminals($firstSymbol) as $terminalClass) {
                    $firstTerminalsList[$terminalClass] = $terminalClass;
                }
            }
        }

        return $firstTerminalsList;
    }
}