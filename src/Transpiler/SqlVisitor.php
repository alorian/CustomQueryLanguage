<?php

namespace App\Transpiler;

use App\Exception\TranspilerUnknownFieldException;
use App\Lexer\AbstractToken;
use App\Lexer\SimpleToken\ParenLeftToken;
use App\Parser\AbstractNode;
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
use RuntimeException;

class SqlVisitor implements VisitorInterface
{

    /** @var array|TranspilerUnknownFieldException[] */
    protected array $exceptionsList = [];

    public function __construct(
        protected FieldsCollection $fieldsCollection
    ) {
    }

    public function getExceptionsList(): array
    {
        return $this->exceptionsList;
    }

    public function resetExceptionsList(): void
    {
        $this->exceptionsList = [];
    }

    public function visit(AbstractNode $node): string
    {
        return match ($node::class) {
            QueryNode::class => $this->visitQueryNode($node),
            ConditionalExpressionNode::class => $this->visitConditionalExpressionNode($node),
            ConditionalTermNode::class => $this->visitConditionalTermNode($node),
            ConditionalFactorNode::class => $this->visitConditionalFactorNode($node),
            ConditionalPrimaryNode::class => $this->visitConditionalPrimaryNode($node),
            SimpleCondExpressionNode::class => $this->visitSimpleCondExpressionNode($node),
            ComparisonExpressionNode::class => $this->visitComparisonExpressionNode($node),
            ComparisonOperatorNode::class => $this->visitComparisonOperatorNode($node),
            ContainsExpressionNode::class => $this->visitContainsExpressionNode($node),
            ContainsOperatorNode::class => $this->visitContainsOperatorNode($node),
            PrimaryNode::class => $this->visitPrimaryNode($node),
            FieldNode::class => $this->visitFieldNode($node),

            default => throw new RuntimeException('Unknown node type'),
        };
    }

    protected function visitQueryNode(AbstractNode $node): string
    {
        $sqlParts = [];

        if (!empty($node->children)) {
            if ($node->children[0] instanceof ConditionalExpressionNode) {
                $sqlParts[] = 'WHERE';
            }
            foreach ($node->children as $child) {
                $sqlParts[] = $child->accept($this);
            }
        }

        return implode(' ', $sqlParts);
    }

    protected function visitConditionalExpressionNode(AbstractNode $node): string
    {
        $sqlParts = [];
        if (count($node->children) === 3) {
            $sqlParts[] = '(';
            $sqlParts[] = $node->children[0]->accept($this);
            $sqlParts[] = $node->children[1]::LEXEME;
            $sqlParts[] = $node->children[2]->accept($this);
            $sqlParts[] = ')';
        } else {
            $sqlParts[] = $node->children[0]->accept($this);
        }

        return implode(' ', $sqlParts);
    }

    protected function visitConditionalTermNode(AbstractNode $node): string
    {
        $sqlParts = [];
        if (count($node->children) === 3) {
            $sqlParts[] = '(';
            $sqlParts[] = $node->children[0]->accept($this);
            $sqlParts[] = $node->children[1]::LEXEME;
            $sqlParts[] = $node->children[2]->accept($this);
            $sqlParts[] = ')';
        } else {
            $sqlParts[] = $node->children[0]->accept($this);
        }

        return implode(' ', $sqlParts);
    }

    protected function visitConditionalFactorNode(AbstractNode $node): string
    {
        $queryPart = '';

        if (!empty($node->children)) {
            if ($node->children[0] instanceof AbstractToken) {
                $queryPart .= $node->children[0]::LEXEME;
                $queryPart .= $node->children[1]->accept($this);
            } else {
                $queryPart .= $node->children[0]->accept($this);
            }
        }

        return $queryPart;
    }

    protected function visitConditionalPrimaryNode(AbstractNode $node): string
    {
        $queryPart = '';

        if (!empty($node->children)) {
            if ($node->children[0] instanceof ParenLeftToken) {
                $queryPart .= $node->children[1]->accept($this);
            } else {
                $queryPart .= $node->children[0]->accept($this);
            }
        }

        return $queryPart;
    }

    protected function visitSimpleCondExpressionNode(AbstractNode $node): string
    {
        $queryPart = '';
        foreach ($node->children as $child) {
            $queryPart .= $child->accept($this);
        }
        return $queryPart;
    }

    protected function visitComparisonExpressionNode(AbstractNode $node): string
    {
        $queryParts = [];
        foreach ($node->children as $child) {
            $queryParts[] = $child->accept($this);
        }
        return implode(' ', $queryParts);
    }

    protected function visitContainsExpressionNode(AbstractNode $node): string
    {
        $queryParts = [];
        foreach ($node->children as $child) {
            $queryParts[] = $child->accept($this);
        }
        return implode(' ', $queryParts);
    }

    protected function visitContainsOperatorNode(AbstractNode $node): string
    {
        //return $node->children[0]::LEXEME;
        return 'like';
    }

    protected function visitComparisonOperatorNode(AbstractNode $node): string
    {
        return $node->children[0]::LEXEME;
    }

    protected function visitPrimaryNode(AbstractNode $node): string
    {
        return "'" . $node->children[0]->value . "'";
    }

    protected function visitFieldNode(AbstractNode $node): string
    {
        $fieldName = strtolower($node->children[0]->value);

        if (!$this->fieldsCollection->fieldExists($fieldName)) {
            $this->exceptionsList[] = new TranspilerUnknownFieldException($node->children[0]);
        }

        return $fieldName;
    }

}