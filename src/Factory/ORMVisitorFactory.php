<?php

namespace AndreasGlaser\DoctrineRql\Factory;

use AndreasGlaser\DoctrineRql\Helper\RQLParser;
use AndreasGlaser\DoctrineRql\Visitor\ORMVisitor;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ORMVisitorFactory
 *
 * @package AndreasGlaser\DoctrineRql\Factory
 * @author  Andreas Glaser
 */
class ORMVisitorFactory
{
    /**
     * @var ORMVisitor
     */
    protected static $visitor;

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param string                     $rqlString
     * @param bool                       $autoRootAlias
     *
     * @author Andreas Glaser
     * @deprecated Use ORMVisitorFactory::appendFiltersOnly() as SELECT is unsupported in 0.1/0.2
     */
    public static function appendAll(QueryBuilder &$qb, $rqlString, $autoRootAlias = true)
    {
        if (!static::$visitor) {
            static::$visitor = new ORMVisitor();
        }

        $qlQuery = RQLParser::parseAll($rqlString);

        static::$visitor->append($qb, $qlQuery, $autoRootAlias);
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param string                     $rqlString
     * @param bool                       $autoRootAlias
     *
     * @author Andreas Glaser
     */
    public static function appendFiltersOnly(QueryBuilder &$qb, $rqlString, $autoRootAlias = true)
    {
        if (!static::$visitor) {
            static::$visitor = new ORMVisitor();
        }

        $qlQuery = RQLParser::parseFiltersOnly($rqlString);

        static::$visitor->append($qb, $qlQuery, $autoRootAlias);
    }
}
