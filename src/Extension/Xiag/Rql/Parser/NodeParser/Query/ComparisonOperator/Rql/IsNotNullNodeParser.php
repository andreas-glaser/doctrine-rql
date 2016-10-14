<?php

namespace AndreasGlaser\DoctrineRql\Extension\Xiag\Rql\Parser\NodeParser\Query\ComparisonOperator\Rql;

use AndreasGlaser\DoctrineRql\Extension\Xiag\Rql\Parser\Node\Query\NullOperator\IsNotNullNode;
use AndreasGlaser\DoctrineRql\Extension\Xiag\Rql\Parser\NodeParser\Query\ComparisonOperator\AbstractNullComparisonRqlNodeParser;

/**
 * Class IsNotNullNodeParser
 *
 * @package AndreasGlaser\DoctrineRql\Extension\Xiag\Rql\Parser\NodeParser\Query\ComparisonOperator\Rql
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
     * @return \AndreasGlaser\DoctrineRql\Extension\Xiag\Rql\Parser\Node\Query\NullOperator\IsNotNullNode
     * @author Andreas Glaser
     */
    protected function createNode($field)
    {
        return new IsNotNullNode($field);
    }
}
