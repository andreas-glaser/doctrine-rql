<?php

namespace AndreasGlaser\DoctrineRql\Helper;

use AndreasGlaser\DoctrineRql\Extension\Xiag\Rql\Parser\NodeParser\Query\ComparisonOperator as ExtensionComparisonOperator;
use AndreasGlaser\DoctrineRql\Factory\LexerFactory;
use Xiag\Rql\Parser as Xiag;
use Xiag\Rql\Parser\NodeParser\Query\ComparisonOperator as XiagComparisonOperator;
use Xiag\Rql\Parser\NodeParser\Query\LogicalOperator as XiagLogicalOperator;

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
     * @return \Xiag\Rql\Parser\Parser
     * @author Andreas Glaser
     * @author Dominic Tubach <dominic.tubach@to.com>
     * @deprecated Use RQLParser::createFiltersOnly() as SELECT is unsupported in 0.1/0.2
     */
    public static function createAll()
    {
        return new Xiag\Parser(Xiag\Parser::createDefaultNodeParser());
    }

    /**
     * Creates WHERE/SORT/LIMIT parser.
     *
     * @return \Xiag\Rql\Parser\Parser
     * @author Andreas Glaser
     * @author Dominic Tubach <dominic.tubach@to.com>
     * @todo   : Rename
     */
    public static function createFiltersOnly()
    {
        $scalarParser = new Xiag\ValueParser\ScalarParser();
        $scalarParser
            ->registerTypeCaster('string', new Xiag\TypeCaster\StringTypeCaster())
            ->registerTypeCaster('integer', new Xiag\TypeCaster\IntegerTypeCaster())
            ->registerTypeCaster('float', new Xiag\TypeCaster\FloatTypeCaster())
            ->registerTypeCaster('boolean', new Xiag\TypeCaster\BooleanTypeCaster());

        $arrayParser = new Xiag\ValueParser\ArrayParser($scalarParser);
        $globParser = new Xiag\ValueParser\GlobParser();
        $fieldParser = new Xiag\ValueParser\FieldParser();
        $integerParser = new Xiag\ValueParser\IntegerParser();

        $queryNodeParser = new Xiag\NodeParser\QueryNodeParser();
        $queryNodeParser
            // RQL/FIQL Common
            ->addNodeParser(new Xiag\NodeParser\Query\GroupNodeParser($queryNodeParser))
            ->addNodeParser(new XiagLogicalOperator\AndNodeParser($queryNodeParser))
            ->addNodeParser(new XiagLogicalOperator\OrNodeParser($queryNodeParser))
            ->addNodeParser(new XiagLogicalOperator\NotNodeParser($queryNodeParser))
            // RQL
            ->addNodeParser(new XiagComparisonOperator\Rql\InNodeParser($fieldParser, $arrayParser))
            ->addNodeParser(new XiagComparisonOperator\Rql\OutNodeParser($fieldParser, $arrayParser))
            ->addNodeParser(new XiagComparisonOperator\Rql\EqNodeParser($fieldParser, $scalarParser))
            ->addNodeParser(new XiagComparisonOperator\Rql\NeNodeParser($fieldParser, $scalarParser))
            ->addNodeParser(new XiagComparisonOperator\Rql\LtNodeParser($fieldParser, $scalarParser))
            ->addNodeParser(new XiagComparisonOperator\Rql\GtNodeParser($fieldParser, $scalarParser))
            ->addNodeParser(new XiagComparisonOperator\Rql\LeNodeParser($fieldParser, $scalarParser))
            ->addNodeParser(new XiagComparisonOperator\Rql\GeNodeParser($fieldParser, $scalarParser))
            ->addNodeParser(new XiagComparisonOperator\Rql\LikeNodeParser($fieldParser, $globParser))
            // FIQL
            ->addNodeParser(new XiagComparisonOperator\Fiql\InNodeParser($fieldParser, $arrayParser))
            ->addNodeParser(new XiagComparisonOperator\Fiql\OutNodeParser($fieldParser, $arrayParser))
            ->addNodeParser(new XiagComparisonOperator\Fiql\EqNodeParser($fieldParser, $scalarParser))
            ->addNodeParser(new XiagComparisonOperator\Fiql\NeNodeParser($fieldParser, $scalarParser))
            ->addNodeParser(new XiagComparisonOperator\Fiql\LtNodeParser($fieldParser, $scalarParser))
            ->addNodeParser(new XiagComparisonOperator\Fiql\GtNodeParser($fieldParser, $scalarParser))
            ->addNodeParser(new XiagComparisonOperator\Fiql\LeNodeParser($fieldParser, $scalarParser))
            ->addNodeParser(new XiagComparisonOperator\Fiql\GeNodeParser($fieldParser, $scalarParser))
            ->addNodeParser(new XiagComparisonOperator\Fiql\LikeNodeParser($fieldParser, $globParser));

        // custom additions
        $queryNodeParser
            // RQL
            ->addNodeParser(new ExtensionComparisonOperator\Rql\IsNullNodeParser($fieldParser))
            ->addNodeParser(new ExtensionComparisonOperator\Rql\IsNotNullNodeParser($fieldParser));

        $parserChain = new Xiag\NodeParserChain();
        $parserChain
            ->addNodeParser($queryNodeParser)
            ->addNodeParser(new Xiag\NodeParser\SortNodeParser($fieldParser))
            ->addNodeParser(new Xiag\NodeParser\LimitNodeParser($integerParser));

        return new Xiag\Parser($parserChain);
    }

    /**
     * Parses given RQL string into an abstract syntax tree (AST).
     *
     * @param \Xiag\Rql\Parser\Parser $parser
     * @param  string                 $rql
     *
     * @return \Xiag\Rql\Parser\Query
     * @author Andreas Glaser
     */
    public static function parse(Xiag\Parser $parser, $rql)
    {
        $lexer = LexerFactory::createLexer();

        return $parser->parse($lexer->tokenize($rql));
    }

    /**
     * Shot cut
     *
     * @param string $rql
     *
     * @return \Xiag\Rql\Parser\Query
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
     * @return \Xiag\Rql\Parser\Query
     * @author Andreas Glaser
     */
    public static function parseFiltersOnly($rql)
    {
        return static::parse(self::createFiltersOnly(), $rql);
    }
}