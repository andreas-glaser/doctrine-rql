<?php

namespace AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\NullOperator;

use AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\AbstractNullOperatorNode;

/**
 * Class IsNotNullNode
 *
 * @package AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\NullOperator
 */
class IsNotNullNode extends AbstractNullOperatorNode
{
    /**
     * @inheritdoc
     */
    public function getNodeName()
    {
        return 'isnotnull';
    }
}
