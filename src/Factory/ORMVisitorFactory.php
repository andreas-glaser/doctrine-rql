<?php

namespace AndreasGlaser\DoctrineRql\Factory;

use AndreasGlaser\DoctrineRql\Mapper\RQL2ORMMapper;
use AndreasGlaser\DoctrineRql\Visitor\ORMVisitor;
use AndreasGlaser\DoctrineRql\Visitor\ORMVisitorInterface;
use AndreasGlaser\DoctrineRql\Visitor\VisitorException;
use Doctrine\ORM\QueryBuilder;
use Graviton\RqlParser\Exception\SyntaxErrorException;

/**
 * Class ORMVisitorFactory
 *
 * @package AndreasGlaser\DoctrineRql\Factory
 */
class ORMVisitorFactory implements ORMVisitorFactoryInterface
{
    /**
     * @param QueryBuilder $qb
     * @param string       $rqlString
     * @param bool         $autoRootAlias
     *
     * @throws SyntaxErrorException
     * @throws VisitorException
     *
     * @deprecated Use RQL2ORMMapper instead.
     */
    public static function appendFiltersOnly(QueryBuilder $qb, string $rqlString, bool $autoRootAlias = true): void
    {
        RQL2ORMMapper::create()->mapToQueryBuilder($qb, $rqlString, $autoRootAlias);
    }

    /**
     * @inheritdoc
     */
    public function createVisitor(QueryBuilder $qb, bool $autoRootAlias): ORMVisitorInterface
    {
        return new ORMVisitor($qb, $autoRootAlias);
    }
}
