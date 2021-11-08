<?php

namespace App\Transpiler;

use App\Exception\TranspilerUnknownFieldException;
use App\Lexer\AbstractToken;
use App\Lexer\SimpleToken\NotContainToken;
use App\Lexer\SimpleToken\ParenLeftToken;
use App\Lexer\TypeToken\EmptyToken;
use App\Lexer\TypeToken\NullToken;
use App\Parser\AbstractNode;
use App\Parser\Node\AlphanumericComparisonExpressionNode;
use App\Parser\Node\AlphanumericValueNode;
use App\Parser\Node\CommaSeparatedSequenceNode;
use App\Parser\Node\ComparisonOperatorNode;
use App\Parser\Node\ConditionalExpressionNode;
use App\Parser\Node\ConditionalFactorNode;
use App\Parser\Node\ConditionalPrimaryNode;
use App\Parser\Node\ConditionalTermNode;
use App\Parser\Node\ContainsExpressionNode;
use App\Parser\Node\ContainsOperatorNode;
use App\Parser\Node\DateComparisonExpression;
use App\Parser\Node\DateValueNode;
use App\Parser\Node\FieldNode;
use App\Parser\Node\InExpressionNode;
use App\Parser\Node\NullComparisonExpressionNode;
use App\Parser\Node\QueryNode;
use App\Parser\Node\SimpleCondExpressionNode;
use DateInterval;
use DateTime;
use Exception;
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

    /**
     * @throws Exception
     */
    public function visit(AbstractNode $node): string|DateInterval
    {
        return match ($node::class) {
            QueryNode::class => $this->visitQueryNode($node),
            ConditionalExpressionNode::class => $this->visitConditionalExpressionNode($node),
            ConditionalTermNode::class => $this->visitConditionalTermNode($node),
            ConditionalFactorNode::class => $this->visitConditionalFactorNode($node),
            ConditionalPrimaryNode::class => $this->visitConditionalPrimaryNode($node),
            SimpleCondExpressionNode::class => $this->visitSimpleCondExpressionNode($node),
            AlphanumericComparisonExpressionNode::class => $this->visitComparisonExpressionNode($node),
            ContainsExpressionNode::class => $this->visitContainsExpressionNode($node),
            DateComparisonExpression::class => $this->visitDateComparisonExpressionNode($node),
            NullComparisonExpressionNode::class => $this->visitNullComparisonExpressionNode($node),
            InExpressionNode::class => $this->visitInExpressionNodeNode($node),
            ComparisonOperatorNode::class => $this->visitComparisonOperatorNode($node),
            ContainsOperatorNode::class => $this->visitContainsOperatorNode($node),
            AlphanumericValueNode::class => $this->visitAlphaNumericValueNode($node),
            FieldNode::class => $this->visitFieldNode($node),
            DateValueNode::class => $this->visitDateValueNode($node),
            CommaSeparatedSequenceNode::class => $this->visitCommaSeparatedSequenceNodeNode($node),

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
        $sqlParts = [];

        if (!empty($node->children)) {
            if ($node->children[0] instanceof AbstractToken) {
                $sqlParts[] = $node->children[0]::LEXEME;
                $sqlParts[] = $node->children[1]->accept($this);
            } else {
                $sqlParts[] = $node->children[0]->accept($this);
            }
        }

        return implode(' ', $sqlParts);
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
        $queryParts[] = $node->children[0]->accept($this);
        $queryParts[] = $node->children[1]->accept($this);
        $queryParts[] = "'%" . $node->children[2]->children[0]->value . "%'";

        return implode(' ', $queryParts);
    }

    protected function visitInExpressionNodeNode(AbstractNode $node): string
    {
        $queryParts = [];
        foreach ($node->children as $child) {
            if ($child instanceof AbstractNode) {
                $queryParts[] = $child->accept($this);
            } else {
                $queryParts[] = $child->value;
            }
        }

        return implode(' ', $queryParts);
    }

    protected function visitNullComparisonExpressionNode(AbstractNode $node): string
    {
        $queryParts = [];
        foreach ($node->children as $child) {
            if ($child instanceof AbstractNode) {
                $queryParts[] = $child->accept($this);
            } elseif ($child instanceof EmptyToken) {
                $queryParts[] = NullToken::LEXEME;
            } else {
                $queryParts[] = $child::LEXEME;
            }
        }

        return implode(' ', $queryParts);
    }

    protected function visitDateComparisonExpressionNode(AbstractNode $node): string
    {
        $queryParts = [];
        $queryParts[] = $node->children[0]->accept($this);// field
        $queryParts[] = $node->children[1]->accept($this);// comparison operator

        $now = new DateTime('now');
        if (count($node->children) === 3) {
            $dateValue = $node->children[2];
            $dateInterval = $dateValue->accept($this);
            $now->add($dateInterval);
        } else {
            $dateValue = $node->children[3];
            $dateInterval = $dateValue->accept($this);
            $now->sub($dateInterval);
        }

        $queryParts[] = "'" . $now->format('Y-m-d H:i:s') . "'";

        return implode(' ', $queryParts);
    }

    protected function visitContainsOperatorNode(AbstractNode $node): string
    {
        if ($node->children[0] instanceof NotContainToken) {
            return 'not like';
        }

        return 'like';
    }

    protected function visitComparisonOperatorNode(AbstractNode $node): string
    {
        return $node->children[0]::LEXEME;
    }

    protected function visitAlphaNumericValueNode(AbstractNode $node): string
    {
        return "'" . $node->children[0]->value . "'";
    }

    /**
     * @throws Exception
     */
    protected function visitDateValueNode(AbstractNode $node): DateInterval
    {
        $val = mb_strtoupper($node->children[0]->value);
        $modifier = mb_substr($node->children[0]->value, -1);

        return match ($modifier) {
            'Y', 'y', 'M', 'D', 'd', 'W', 'w' => new DateInterval('P' . $val),
            default => new DateInterval('PT' . $val),
        };
    }

    protected function visitFieldNode(AbstractNode $node): string
    {
        $fieldName = mb_strtolower($node->children[0]->value);

        if (!$this->fieldsCollection->fieldExists($fieldName)) {
            $this->exceptionsList[] = new TranspilerUnknownFieldException($node->children[0]);
        }

        return $fieldName;
    }

    protected function visitCommaSeparatedSequenceNodeNode(AbstractNode $node): string
    {
        $queryParts = [];
        foreach ($node->children as $child) {
            if ($child instanceof AbstractNode) {
                $queryParts[] = $child->accept($this);
            } else {
                $queryParts[] = $child::LEXEME;
            }
        }

        return implode(' ', $queryParts);
    }


}