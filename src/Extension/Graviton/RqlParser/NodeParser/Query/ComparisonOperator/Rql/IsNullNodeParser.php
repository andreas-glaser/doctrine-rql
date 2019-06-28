<?php

namespace AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\NodeParser\Query\ComparisonOperator\Rql;

use AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\NullOperator\IsNullNode;
use AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\NodeParser\Query\ComparisonOperator\AbstractNullComparisonRqlNodeParser;

/**
 * Class IsNullNodeParser
 *
 * @package AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\NodeParser\Query\ComparisonOperator\Rql
 * @author  Andreas Glaser
 */
class IsNullNodeParser extends AbstractNullComparisonRqlNodeParser
{
    /**
     * @inheritdoc
     */
    protected function getOperatorName()
    {
        return 'isnull';
    }

    /**
     * @param $field
     *
     * @return \AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\NullOperator\IsNullNode
     * @author Andreas Glaser
     */
    protected function createNode($field)
    {
        return new IsNullNode($field);
    }
}
