<?php

namespace App\Tests\Parser;

use App\Exception\ParserUnexpectedTokenException;
use App\Lexer\KeywordToken\AndToken;
use App\Lexer\KeywordToken\OrToken;
use App\Lexer\Lexer;
use App\Lexer\SimpleToken\EqualToken;
use App\Lexer\SimpleToken\GreaterEqualToken;
use App\Lexer\SimpleToken\GreaterToken;
use App\Lexer\SimpleToken\LessEqualToken;
use App\Lexer\SimpleToken\LessToken;
use App\Lexer\SimpleToken\NotEqualToken;
use App\Lexer\SimpleToken\ParenLeftToken;
use App\Lexer\SimpleToken\ParenRightToken;
use App\Lexer\TypeToken\StringToken;
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
use App\Parser\Parser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ParserTest extends KernelTestCase
{
    protected static ?Lexer $lexer;

    protected static ?Parser $parser;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::bootKernel();
        static::$parser = self::getContainer()->get(Parser::class);
        static::$lexer = self::getContainer()->get(Lexer::class);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        static::$parser = null;
        static::$lexer = null;
    }

    public function unknownTokenProvider(): array
    {
        return [
            ['= asd'],
            ['asd or or asd'],
            ['(('],
        ];
    }

    /**
     * @dataProvider unknownTokenProvider
     */
    public function testUnexpectedTokenException(string $query): void
    {
        $this->expectException(ParserUnexpectedTokenException::class);

        $tokensList = static::$lexer->analyze($query);
        static::$parser->parse($tokensList);
    }

    public function testSimpleString(): void
    {
        $query = 'name = test';
        $tokensList = static::$lexer->analyze($query);
        $queryNode = static::$parser->parse($tokensList);

        // QueryNode
        $this->assertInstanceOf(QueryNode::class, $queryNode);
        $this->assertCount(1, $queryNode->children);

        // ConditionalExpressionNode
        $child = reset($queryNode->children);
        $this->assertInstanceOf(ConditionalExpressionNode::class, $child);
        $this->assertCount(1, $child->children);

        // ConditionalTermNode
        $child = reset($child->children);
        $this->assertInstanceOf(ConditionalTermNode::class, $child);
        $this->assertCount(1, $child->children);

        // ConditionalFactorNode
        $child = reset($child->children);
        $this->assertInstanceOf(ConditionalFactorNode::class, $child);
        $this->assertCount(1, $child->children);

        // ConditionalFactorNode
        $child = reset($child->children);
        $this->assertInstanceOf(ConditionalPrimaryNode::class, $child);
        $this->assertCount(1, $child->children);

        // SimpleCondExpressionNode
        $child = reset($child->children);
        $this->assertInstanceOf(SimpleCondExpressionNode::class, $child);
        $this->assertCount(1, $child->children);

        // ComparisonExpressionNode
        $comparisonExpressionNode = reset($child->children);
        $this->assertInstanceOf(ComparisonExpressionNode::class, $comparisonExpressionNode);
        $this->assertCount(3, $comparisonExpressionNode->children);

        // FieldNode
        $fieldNode = $comparisonExpressionNode->children[0];
        $this->assertInstanceOf(FieldNode::class, $fieldNode);
        $this->assertEquals('name', $fieldNode->children[0]->value);
        $this->assertContainsOnlyInstancesOf(StringToken::class, $fieldNode->children);

        // ComparisonOperatorNode
        $comparisonOperatorNode = $comparisonExpressionNode->children[1];
        $this->assertInstanceOf(ComparisonOperatorNode::class, $comparisonOperatorNode);
        $this->assertContainsOnlyInstancesOf(EqualToken::class, $comparisonOperatorNode->children);

        // PrimaryNode
        $primaryNode = $comparisonExpressionNode->children[2];
        $this->assertInstanceOf(PrimaryNode::class, $primaryNode);
        $this->assertEquals('test', $primaryNode->children[0]->value);
        $this->assertContainsOnlyInstancesOf(StringToken::class, $primaryNode->children);
    }

    public function testAndCondition(): void
    {
        $query = 'name = test and name1 = test1';
        $tokensList = static::$lexer->analyze($query);
        $node = static::$parser->parse($tokensList);

        while (!$node instanceof ConditionalTermNode && !empty($node->children)) {
            $child = reset($node->children);
            $node = $child;
        }

        $this->assertInstanceOf(ConditionalTermNode::class, $node);
        $this->assertInstanceOf(ConditionalTermNode::class, $node->children[0]);
        $this->assertInstanceOf(AndToken::class, $node->children[1]);
        $this->assertInstanceOf(ConditionalFactorNode::class, $node->children[2]);
    }

    public function testOrCondition(): void
    {
        $query = 'name = test or name1 = test1';
        $tokensList = static::$lexer->analyze($query);
        $node = static::$parser->parse($tokensList);

        while (!$node instanceof ConditionalExpressionNode && !empty($node->children)) {
            $node = reset($node->children);
        }

        $this->assertInstanceOf(ConditionalExpressionNode::class, $node);
        $this->assertInstanceOf(ConditionalExpressionNode::class, $node->children[0]);
        $this->assertInstanceOf(OrToken::class, $node->children[1]);
        $this->assertInstanceOf(ConditionalTermNode::class, $node->children[2]);
    }

    public function testBrackets(): void
    {
        $query = '(name = test)';
        $tokensList = static::$lexer->analyze($query);
        $node = static::$parser->parse($tokensList);

        while (!$node instanceof ConditionalPrimaryNode && !empty($node->children)) {
            $node = reset($node->children);
        }

        $this->assertInstanceOf(ConditionalPrimaryNode::class, $node);
        $this->assertInstanceOf(ParenLeftToken::class, $node->children[0]);
        $this->assertInstanceOf(ConditionalExpressionNode::class, $node->children[1]);
        $this->assertInstanceOf(ParenRightToken::class, $node->children[2]);
    }

    public function comparisonExpressionsProvider(): array
    {
        $dataSets = [];

        $operators = [
            EqualToken::class,
            NotEqualToken::class,
            LessToken::class,
            LessEqualToken::class,
            GreaterToken::class,
            GreaterEqualToken::class,
        ];
        foreach ($operators as $opClassName) {
            $dataSets[] = ['name ' . $opClassName::LEXEME . ' test'];
        }

        return $dataSets;
    }

    /**
     * @dataProvider comparisonExpressionsProvider
     */
    public function testComparisonExpression(string $query): void
    {
        $tokensList = static::$lexer->analyze($query);
        $node = static::$parser->parse($tokensList);

        while (!$node instanceof ComparisonExpressionNode && !empty($node->children)) {
            $node = reset($node->children);
        }

        $this->assertInstanceOf(ComparisonExpressionNode::class, $node);
        $this->assertInstanceOf(FieldNode::class, $node->children[0]);
        $this->assertInstanceOf(ComparisonOperatorNode::class, $node->children[1]);
        $this->assertInstanceOf(PrimaryNode::class, $node->children[2]);
    }

    public function testContainsExpression(): void
    {
        $query = 'name ~ test';
        $tokensList = static::$lexer->analyze($query);
        $node = static::$parser->parse($tokensList);

        while (!$node instanceof ContainsExpressionNode && !empty($node->children)) {
            $node = reset($node->children);
        }

        $this->assertInstanceOf(ContainsExpressionNode::class, $node);
        $this->assertInstanceOf(FieldNode::class, $node->children[0]);
        $this->assertInstanceOf(ContainsOperatorNode::class, $node->children[1]);
        $this->assertInstanceOf(PrimaryNode::class, $node->children[2]);
    }

    public function testTokensWithoutEoL(): void
    {
        $query = 'name = test';
        $tokensList = static::$lexer->analyze($query);
        $eolToken = array_pop($tokensList);

        $this->expectException(ParserUnexpectedTokenException::class);
        $node = static::$parser->parse($tokensList);
    }

}
