<?php

namespace AndreasGlaser\DoctrineRql\Extension\Xiag\Rql\Parser\NodeParser\Query\ComparisonOperator;

use AndreasGlaser\DoctrineRql\Extension\Xiag\Rql\Parser\Node\Query\AbstractNullOperatorNode;
use Xiag\Rql\Parser\NodeParserInterface;
use Xiag\Rql\Parser\SubParserInterface;
use Xiag\Rql\Parser\Token;
use Xiag\Rql\Parser\TokenStream;

/**
 * Class AbstractNullComparisonRqlNodeParser
 *
 * @package AndreasGlaser\DoctrineRql\Extension\Xiag\Rql\Parser\NodeParser\Query\ComparisonOperator
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
     * @param \Xiag\Rql\Parser\TokenStream $tokenStream
     *
     * @return \AndreasGlaser\DoctrineRql\Extension\Xiag\Rql\Parser\Node\Query\AbstractNullOperatorNode
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
     * @param \Xiag\Rql\Parser\TokenStream $tokenStream
     *
     * @return bool
     * @author Andreas Glaser
     */
    public function supports(TokenStream $tokenStream)
    {
        return $tokenStream->test(Token::T_OPERATOR, $this->getOperatorName());
    }
}
