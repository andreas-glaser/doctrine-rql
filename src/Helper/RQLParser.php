<?php

namespace AndreasGlaser\DoctrineRql\Helper;

use AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Lexer;
use AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\NodeParser\Query\ComparisonOperator as ExtensionComparisonOperator;
use Graviton\RqlParser as Graviton;
use Graviton\RqlParser\NodeParser\Query\ComparisonOperator as GravitonComparisonOperator;
use Graviton\RqlParser\NodeParser\Query\LogicalOperator as GravitonLogicalOperator;

/**
 * Class RQLParser
 *
 * @package AndreasGlaser\DoctrineRql\Helper
 * @author  Andreas Glaser
 * @author  Dominic Tubach <dominic.tubach@to.com>
 */
class RQLParser
{
    /**
     * Creates SELECT/WHERE/SORT/LIMIT parser.
     *
     * @return \Graviton\RqlParser\Parser
     * @author Andreas Glaser
     * @author Dominic Tubach <dominic.tubach@to.com>
     * @deprecated Use RQLParser::createFiltersOnly() as SELECT is unsupported in 0.1/0.2
     */
    public static function createAll()
    {
        return new Graviton\Parser(Graviton\Parser::createDefaultNodeParser());
    }

    /**
     * Creates WHERE/SORT/LIMIT parser.
     *
     * @return \Graviton\RqlParser\Parser
     * @author Andreas Glaser
     * @author Dominic Tubach <dominic.tubach@to.com>
     * @todo   : Rename
     */
    public static function createFiltersOnly()
    {
        $scalarParser = new Graviton\ValueParser\ScalarParser();
        $scalarParser
            ->registerTypeCaster('string', new Graviton\TypeCaster\StringTypeCaster())
            ->registerTypeCaster('integer', new Graviton\TypeCaster\IntegerTypeCaster())
            ->registerTypeCaster('float', new Graviton\TypeCaster\FloatTypeCaster())
            ->registerTypeCaster('boolean', new Graviton\TypeCaster\BooleanTypeCaster());

        $arrayParser = new Graviton\ValueParser\ArrayParser($scalarParser);
        $globParser = new Graviton\ValueParser\GlobParser();
        $fieldParser = new Graviton\ValueParser\FieldParser();
        $integerParser = new Graviton\ValueParser\IntegerParser();

        $queryNodeParser = new Graviton\NodeParser\QueryNodeParser();
        $queryNodeParser
            // RQL/FIQL Common
            ->addNodeParser(new Graviton\NodeParser\Query\GroupNodeParser($queryNodeParser))
            ->addNodeParser(new GravitonLogicalOperator\AndNodeParser($queryNodeParser))
            ->addNodeParser(new GravitonLogicalOperator\OrNodeParser($queryNodeParser))
            ->addNodeParser(new GravitonLogicalOperator\NotNodeParser($queryNodeParser))
            // RQL
            ->addNodeParser(new GravitonComparisonOperator\Rql\InNodeParser($fieldParser, $arrayParser))
            ->addNodeParser(new GravitonComparisonOperator\Rql\OutNodeParser($fieldParser, $arrayParser))
            ->addNodeParser(new GravitonComparisonOperator\Rql\EqNodeParser($fieldParser, $scalarParser))
            ->addNodeParser(new GravitonComparisonOperator\Rql\NeNodeParser($fieldParser, $scalarParser))
            ->addNodeParser(new GravitonComparisonOperator\Rql\LtNodeParser($fieldParser, $scalarParser))
            ->addNodeParser(new GravitonComparisonOperator\Rql\GtNodeParser($fieldParser, $scalarParser))
            ->addNodeParser(new GravitonComparisonOperator\Rql\LeNodeParser($fieldParser, $scalarParser))
            ->addNodeParser(new GravitonComparisonOperator\Rql\GeNodeParser($fieldParser, $scalarParser))
            ->addNodeParser(new GravitonComparisonOperator\Rql\LikeNodeParser($fieldParser, $globParser))
            // FIQL
            ->addNodeParser(new GravitonComparisonOperator\Fiql\InNodeParser($fieldParser, $arrayParser))
            ->addNodeParser(new GravitonComparisonOperator\Fiql\OutNodeParser($fieldParser, $arrayParser))
            ->addNodeParser(new GravitonComparisonOperator\Fiql\EqNodeParser($fieldParser, $scalarParser))
            ->addNodeParser(new GravitonComparisonOperator\Fiql\NeNodeParser($fieldParser, $scalarParser))
            ->addNodeParser(new GravitonComparisonOperator\Fiql\LtNodeParser($fieldParser, $scalarParser))
            ->addNodeParser(new GravitonComparisonOperator\Fiql\GtNodeParser($fieldParser, $scalarParser))
            ->addNodeParser(new GravitonComparisonOperator\Fiql\LeNodeParser($fieldParser, $scalarParser))
            ->addNodeParser(new GravitonComparisonOperator\Fiql\GeNodeParser($fieldParser, $scalarParser))
            ->addNodeParser(new GravitonComparisonOperator\Fiql\LikeNodeParser($fieldParser, $globParser));

        // custom additions
        $queryNodeParser
            // RQL
            ->addNodeParser(new ExtensionComparisonOperator\Rql\IsNullNodeParser($fieldParser))
            ->addNodeParser(new ExtensionComparisonOperator\Rql\IsNotNullNodeParser($fieldParser));

        $parserChain = new Graviton\NodeParserChain();
        $parserChain
            ->addNodeParser($queryNodeParser)
            ->addNodeParser(new Graviton\NodeParser\SortNodeParser($fieldParser))
            ->addNodeParser(new Graviton\NodeParser\LimitNodeParser($integerParser));

        return new Graviton\Parser($parserChain);
    }

    /**
     * Parses given RQL string into an abstract syntax tree (AST).
     *
     * @param \Graviton\RqlParser\Parser $parser
     * @param  string                 $rql
     *
     * @return \Graviton\RqlParser\Query
     * @author Andreas Glaser
     */
    public static function parse(Graviton\Parser $parser, $rql)
    {
        $lexer = new Lexer();

        return $parser->parse($lexer->tokenize($rql));
    }

    /**
     * Shot cut
     *
     * @param string $rql
     *
     * @return \Graviton\RqlParser\Query
     * @author Andreas Glaser
     * @deprecated Use RQLParser::parseFiltersOnly() as SELECT is unsupported in 0.1/0.2
     */
    public static function parseAll($rql)
    {
        return static::parse(self::createAll(), $rql);
    }

    /**
     * Short cut
     *
     * @param $rql
     *
     * @return \Graviton\RqlParser\Query
     * @author Andreas Glaser
     */
    public static function parseFiltersOnly($rql)
    {
        return static::parse(self::createFiltersOnly(), $rql);
    }
}