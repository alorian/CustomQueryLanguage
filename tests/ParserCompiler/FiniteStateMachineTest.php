<?php

namespace App\Tests\ParserCompiler;

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
use App\ParserCompiler\FiniteStateMachine;
use App\ParserCompiler\GrammarRule;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FiniteStateMachineTest extends KernelTestCase
{

    protected static ?FiniteStateMachine $finiteStateMachine;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::bootKernel();

        $nonTerminalsList = [
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

        // making rules collection
        $grammarRuleIndex = 0;
        $grammarRulesList = [];
        foreach ($nonTerminalsList as $nonTerminalClassName) {
            foreach ($nonTerminalClassName::RULES as $RULE_ITEMS) {
                $grammarRule = new GrammarRule($nonTerminalClassName, $RULE_ITEMS, $grammarRuleIndex);
                $grammarRulesList[] = $grammarRule;
                $grammarRuleIndex++;
            }
        }

        static::$finiteStateMachine = self::getContainer()->get(FiniteStateMachine::class);
        static::$finiteStateMachine->setGrammarRulesList($grammarRulesList);
        static::$finiteStateMachine->build();
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        static::$finiteStateMachine = null;
    }

    public function testGeneralWork(): void
    {
        $statesList = static::$finiteStateMachine->getStatesList();
        $transitionsList = static::$finiteStateMachine->getStatesList();

        $this->assertNotEmpty($statesList);
        $this->assertNotEmpty($transitionsList);
    }
}