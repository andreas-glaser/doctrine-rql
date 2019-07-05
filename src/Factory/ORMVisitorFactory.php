<?php

namespace AndreasGlaser\DoctrineRql\Factory;

use AndreasGlaser\DoctrineRql\Helper\RQLParser;
use AndreasGlaser\DoctrineRql\Visitor\ORMVisitor;
use Doctrine\ORM\QueryBuilder;

/**
 * Class ORMVisitorFactory
 *
 * @package AndreasGlaser\DoctrineRql\Factory
 */
class ORMVisitorFactory
{
    /**
     * @var ORMVisitor
     */
    protected static $visitor;

    /**
     * @param QueryBuilder $qb
     * @param string       $rqlString
     * @param bool         $autoRootAlias
     *
     * @throws \AndreasGlaser\DoctrineRql\Visitor\VisitorException
     */
    public static function appendFiltersOnly(QueryBuilder &$qb, string $rqlString, bool $autoRootAlias = true): void
    {
        if (!static::$visitor) {
            static::$visitor = new ORMVisitor();
        }

        $qlQuery = RQLParser::parseFiltersOnly($rqlString);

        static::$visitor->append($qb, $qlQuery, $autoRootAlias);
    }
}
