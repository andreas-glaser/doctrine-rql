<?php

namespace AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\NodeParser\Query\ComparisonOperator\Rql;

use AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\AbstractNullOperatorNode;
use AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\NullOperator\IsNullNode;
use AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\NodeParser\Query\ComparisonOperator\AbstractNullComparisonRqlNodeParser;

/**
 * Class IsNullNodeParser
 *
 * @package AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\NodeParser\Query\ComparisonOperator\Rql
 */
class IsNullNodeParser extends AbstractNullComparisonRqlNodeParser
{
    /**
     * @inheritdoc
     */
    protected function getOperatorName(): string
    {
        return 'isnull';
    }

    /**
     * @param string $field
     *
     * @return IsNullNode
     */
    protected function createNode(string $field): AbstractNullOperatorNode
    {
        return new IsNullNode($field);
    }
}
