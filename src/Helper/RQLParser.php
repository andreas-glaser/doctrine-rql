<?php

namespace AndreasGlaser\DoctrineRql\Helper;

use AndreasGlaser\DoctrineRql\Extension\Xiag\TokenParser\Query\Basic\IsNotNullTokenParser;
use AndreasGlaser\DoctrineRql\Extension\Xiag\TokenParser\Query\Basic\IsNullTokenParser;
use Xiag\Rql\Parser as Xiag;

/**
 * Class RQLParser
 *
 * @package AndreasGlaser\DoctrineRql\Helper
 * @author  Andreas Glaser
 */
class RQLParser
{
    /**
     * Creates SELECT/WHERE/SORT/LIMIT parser.
     *
     * @return \Xiag\Rql\Parser\Parser
     * @author Andreas Glaser
     */
    public static function createAll()
    {
        return Xiag\Parser::createDefault();
    }

    /**
     * Creates WHERE/SORT/LIMIT parser.
     *
     * @return Xiag\Parser
     * @author Andreas Glaser
     */
    public static function createFiltersOnly()
    {
        $queryTokenParser = new Xiag\TokenParserGroup();
        $queryTokenParser
            ->addTokenParser(new Xiag\TokenParser\Query\GroupTokenParser($queryTokenParser))
            ->addTokenParser(new Xiag\TokenParser\Query\Basic\LogicOperator\AndTokenParser($queryTokenParser))
            ->addTokenParser(new Xiag\TokenParser\Query\Basic\LogicOperator\OrTokenParser($queryTokenParser))
            ->addTokenParser(new Xiag\TokenParser\Query\Basic\LogicOperator\NotTokenParser($queryTokenParser))
            ->addTokenParser(new Xiag\TokenParser\Query\Basic\ArrayOperator\InTokenParser())
            ->addTokenParser(new Xiag\TokenParser\Query\Basic\ArrayOperator\OutTokenParser())
            ->addTokenParser(new Xiag\TokenParser\Query\Basic\ScalarOperator\EqTokenParser())
            ->addTokenParser(new Xiag\TokenParser\Query\Basic\ScalarOperator\NeTokenParser())
            ->addTokenParser(new Xiag\TokenParser\Query\Basic\ScalarOperator\LtTokenParser())
            ->addTokenParser(new Xiag\TokenParser\Query\Basic\ScalarOperator\GtTokenParser())
            ->addTokenParser(new Xiag\TokenParser\Query\Basic\ScalarOperator\LeTokenParser())
            ->addTokenParser(new Xiag\TokenParser\Query\Basic\ScalarOperator\GeTokenParser())
            ->addTokenParser(new Xiag\TokenParser\Query\Basic\ScalarOperator\LikeTokenParser())
            ->addTokenParser(new Xiag\TokenParser\Query\Fiql\ArrayOperator\InTokenParser())
            ->addTokenParser(new Xiag\TokenParser\Query\Fiql\ArrayOperator\OutTokenParser())
            ->addTokenParser(new Xiag\TokenParser\Query\Fiql\ScalarOperator\EqTokenParser())
            ->addTokenParser(new Xiag\TokenParser\Query\Fiql\ScalarOperator\NeTokenParser())
            ->addTokenParser(new Xiag\TokenParser\Query\Fiql\ScalarOperator\LtTokenParser())
            ->addTokenParser(new Xiag\TokenParser\Query\Fiql\ScalarOperator\GtTokenParser())
            ->addTokenParser(new Xiag\TokenParser\Query\Fiql\ScalarOperator\LeTokenParser())
            ->addTokenParser(new Xiag\TokenParser\Query\Fiql\ScalarOperator\GeTokenParser())
            ->addTokenParser(new Xiag\TokenParser\Query\Fiql\ScalarOperator\LikeTokenParser());

        // custom additions
        $queryTokenParser
            ->addTokenParser(new IsNullTokenParser())
            ->addTokenParser(new IsNotNullTokenParser());

        return (new Xiag\Parser(
            (new Xiag\ExpressionParser())
                ->registerTypeCaster('string', new Xiag\TypeCaster\StringTypeCaster())
                ->registerTypeCaster('integer', new Xiag\TypeCaster\IntegerTypeCaster())
                ->registerTypeCaster('float', new Xiag\TypeCaster\FloatTypeCaster())
                ->registerTypeCaster('boolean', new Xiag\TypeCaster\BooleanTypeCaster())
        ))
            ->addTokenParser($queryTokenParser)
            ->addTokenParser(new Xiag\TokenParser\SortTokenParser())
            ->addTokenParser(new Xiag\TokenParser\LimitTokenParser());
    }

    /**
     * Parses given RQL string into an abstract syntax tree (AST).
     *
     * @param \Xiag\Rql\Parser\Parser $parser
     * @param string                  $rql
     *
     * @return \Xiag\Rql\Parser\Query
     * @author Andreas Glaser
     */
    public static function parse(Xiag\Parser $parser, $rql)
    {
        $lexer = new Xiag\Lexer();

        return $parser->parse($lexer->tokenize($rql));
    }

    /**
     * Shot cut
     *
     * @param string $rql
     *
     * @return \Xiag\Rql\Parser\Query
     * @author Andreas Glaser
     */
    public static function parseAll($rql)
    {
        return static::parse(self::createAll(), $rql);
    }

    /**
     * Short cut
     *
     * @param string $rql
     *
     * @return \Xiag\Rql\Parser\Query
     * @author Andreas Glaser
     */
    public static function parseFiltersOnly($rql)
    {
        return static::parse(self::createFiltersOnly(), $rql);
    }
}