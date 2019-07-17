<?php

namespace AndreasGlaser\DoctrineRql\Visitor;

use AndreasGlaser\DoctrineRql\Visitor\VisitorException;
use Graviton\RqlParser\Query as RqlQuery;

interface ORMVisitorInterface
{
    /**
     * @param RqlQuery $query
     *
     * @throws VisitorException
     */
    public function visitQuery(RqlQuery $query): void;
}
