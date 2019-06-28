<?php

namespace AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\NodeParser\Query\ComparisonOperator;

use AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\AbstractNullOperatorNode;
use Graviton\RqlParser\NodeParserInterface;
use Graviton\RqlParser\SubParserInterface;
use Graviton\RqlParser\Token;
use Graviton\RqlParser\TokenStream;

/**
 * Class AbstractNullComparisonRqlNodeParser
 *
 * @package AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\NodeParser\Query\ComparisonOperator
 * @author  Andreas Glaser
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
     * @param $field
     *
     * @return AbstractNullOperatorNode
     * @author Andreas Glaser
     */
    abstract protected function createNode($field);

    /**
     * @return string
     * @author Andreas Glaser
     */
    abstract protected function getOperatorName();

    /**
     * @param \Graviton\RqlParser\TokenStream $tokenStream
     *
     * @return \AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\AbstractNullOperatorNode
     * @author Andreas Glaser
     */
    public function parse(TokenStream $tokenStream)
    {
        $tokenStream->expect(Token::T_OPERATOR, $this->getOperatorName());
        $tokenStream->expect(Token::T_OPEN_PARENTHESIS);
        $field = $this->fieldNameParser->parse($tokenStream);
        $tokenStream->expect(Token::T_CLOSE_PARENTHESIS);

        return $this->createNode($field);
    }

    /**
     * @param \Graviton\RqlParser\TokenStream $tokenStream
     *
     * @return bool
     * @author Andreas Glaser
     */
    public function supports(TokenStream $tokenStream)
    {
        return $tokenStream->test(Token::T_OPERATOR, $this->getOperatorName());
    }
}
