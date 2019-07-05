<?php

namespace AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\NodeParser\Query\ComparisonOperator;

use AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\AbstractNullOperatorNode;
use Graviton\RqlParser\AbstractNode;
use Graviton\RqlParser\NodeParserInterface;
use Graviton\RqlParser\SubParserInterface;
use Graviton\RqlParser\Token;
use Graviton\RqlParser\TokenStream;

/**
 * Class AbstractNullComparisonRqlNodeParser
 *
 * @package AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\NodeParser\Query\ComparisonOperator
 */
abstract class AbstractNullComparisonRqlNodeParser implements NodeParserInterface
{
    /**
     * @var SubParserInterface
     */
    protected $fieldNameParser;

    /**
     * @param SubParserInterface $fieldNameParser
     */
    public function __construct(SubParserInterface $fieldNameParser)
    {
        $this->fieldNameParser = $fieldNameParser;
    }

    /**
     * @param string $field
     *
     * @return AbstractNullOperatorNode
     */
    abstract protected function createNode(string $field): AbstractNullOperatorNode;

    /**
     * @return string
     */
    abstract protected function getOperatorName(): string;

    /**
     * @param TokenStream $tokenStream
     *
     * @return AbstractNullOperatorNode
     */
    public function parse(TokenStream $tokenStream): AbstractNode
    {
        $tokenStream->expect(Token::T_OPERATOR, $this->getOperatorName());
        $tokenStream->expect(Token::T_OPEN_PARENTHESIS);
        $field = $this->fieldNameParser->parse($tokenStream);
        $tokenStream->expect(Token::T_CLOSE_PARENTHESIS);

        return $this->createNode($field);
    }

    /**
     * @param TokenStream $tokenStream
     *
     * @return bool
     */
    public function supports(TokenStream $tokenStream): bool
    {
        return $tokenStream->test(Token::T_OPERATOR, $this->getOperatorName());
    }
}
