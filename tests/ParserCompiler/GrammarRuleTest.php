<?php

namespace App\Tests\ParserCompiler;

use App\Lexer\SimpleToken\ParenLeftToken;
use App\Lexer\SimpleToken\ParenRightToken;
use App\Parser\Node\ConditionalPrimaryNode;
use App\Parser\Node\QueryNode;
use App\ParserCompiler\GrammarRule;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GrammarRuleTest extends KernelTestCase
{
    public function testBasicOperations(): void
    {
        $kernel = self::bootKernel();

        $grammarRule = new GrammarRule(ConditionalPrimaryNode::class, ConditionalPrimaryNode::RULES[1], 0);

        $this->assertFalse($grammarRule->hasItem(QueryNode::class));
        $this->assertNull($grammarRule->nextItemAfter(QueryNode::class));
        $this->assertEquals(ParenLeftToken::class, $grammarRule->first());
        $this->assertEquals(ParenRightToken::class, $grammarRule->last());
        $this->assertEquals(3, $grammarRule->length());

        $this->assertEquals(ParenRightToken::class, $grammarRule->get(2));
        $this->assertNull($grammarRule->get(3));
    }


}
