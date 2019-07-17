<?php

namespace AndreasGlaser\DoctrineRql\Factory;

use AndreasGlaser\DoctrineRql\Visitor\ORMVisitorInterface;
use Doctrine\ORM\QueryBuilder;

interface ORMVisitorFactoryInterface
{
    /**
     * @param QueryBuilder $qb
     * @param bool         $autoRootAlias true to prepend the root alias of the
     *     query builder to the field names from the RQL query, false otherwise.
     *
     * @return ORMVisitorInterface
     */
    public function createVisitor(QueryBuilder $qb, bool $autoRootAlias): ORMVisitorInterface;
}
