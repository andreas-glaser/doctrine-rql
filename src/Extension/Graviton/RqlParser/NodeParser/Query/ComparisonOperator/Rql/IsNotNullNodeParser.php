<?php

namespace AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\NodeParser\Query\ComparisonOperator\Rql;

use AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\NullOperator\IsNotNullNode;
use AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\NodeParser\Query\ComparisonOperator\AbstractNullComparisonRqlNodeParser;

/**
 * Class IsNotNullNodeParser
 *
 * @package AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\NodeParser\Query\ComparisonOperator\Rql
 * @author  Andreas Glaser
 */
class IsNotNullNodeParser extends AbstractNullComparisonRqlNodeParser
{
    /**
     * @inheritdoc
     */
    protected function getOperatorName()
    {
        return 'isnotnull';
    }

    /**
     * @param $field
     *
     * @return \AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\NullOperator\IsNotNullNode
     * @author Andreas Glaser
     */
    protected function createNode($field)
    {
        return new IsNotNullNode($field);
    }
}
