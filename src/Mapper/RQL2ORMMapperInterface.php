<?php

namespace AndreasGlaser\DoctrineRql\Mapper;

use AndreasGlaser\DoctrineRql\Visitor\VisitorException;
use Doctrine\ORM\QueryBuilder;
use Graviton\RqlParser\Exception\SyntaxErrorException;

interface RQL2ORMMapperInterface
{
    /**
     * Maps the filters as well as limit and sort operators from the RQL string
     * to the query builder. Using an unsupported operator will throw a
     * SyntaxErrorException.
     *
     * @param QueryBuilder $qb
     * @param string       $rql
     * @param bool         $autoRootAlias true to prepend the root alias of the
     *     query builder to the field names from the RQL string, false otherwise.
     *
     * @throws SyntaxErrorException
     * @throws VisitorException
     */
    public function mapToQueryBuilder(QueryBuilder $qb, string $rql, bool $autoRootAlias = true): void;
}
