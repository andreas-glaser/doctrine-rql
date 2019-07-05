<?php

namespace AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\NodeParser\Query\ComparisonOperator\Rql;

use AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\AbstractNullOperatorNode;
use AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\NullOperator\IsNotNullNode;
use AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\NodeParser\Query\ComparisonOperator\AbstractNullComparisonRqlNodeParser;

/**
 * Class IsNotNullNodeParser
 *
 * @package AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\NodeParser\Query\ComparisonOperator\Rql
 */
class IsNotNullNodeParser extends AbstractNullComparisonRqlNodeParser
{
    /**
     * @inheritdoc
     */
    protected function getOperatorName(): string
    {
        return 'isnotnull';
    }

    /**
     * @param string $field
     *
     * @return IsNotNullNode
     */
    protected function createNode(string $field): AbstractNullOperatorNode
    {
        return new IsNotNullNode($field);
    }
}
