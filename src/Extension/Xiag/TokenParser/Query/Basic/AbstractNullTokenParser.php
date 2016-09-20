<?php

namespace AndreasGlaser\DoctrineRql\Extension\Xiag\TokenParser\Query\Basic;

use AndreasGlaser\DoctrineRql\Extension\Xiag\Node\Query\AbstractNullComparisonOperatorNode;
use Xiag\Rql\Parser\Token;
use Xiag\Rql\Parser\TokenParser\Query\AbstractBasicTokenParser;
use Xiag\Rql\Parser\TokenStream;

/**
 * Class AbstractNullTokenParser
 *
 * @package AndreasGlaser\DoctrineRql\Extension\Xiag\TokenParser\Query\Basic
 * @author  Andreas Glaser
 */
abstract class AbstractNullTokenParser extends AbstractBasicTokenParser
{

    /**
     * @param $field
     *
     * @return AbstractNullComparisonOperatorNode
     * @author Andreas Glaser
     */
    abstract protected function createNode($field);

    /**
     * @param \Xiag\Rql\Parser\TokenStream $tokenStream
     *
     * @return mixed
     * @author Andreas Glaser
     */
    public function parse(TokenStream $tokenStream)
    {
        $tokenStream->expect(Token::T_OPERATOR, $this->getOperatorName());
        $tokenStream->expect(Token::T_OPEN_PARENTHESIS);
        $field = $tokenStream->expect(Token::T_STRING)->getValue();
        $tokenStream->expect(Token::T_CLOSE_PARENTHESIS);

        return $this->createNode($field);
    }
}
