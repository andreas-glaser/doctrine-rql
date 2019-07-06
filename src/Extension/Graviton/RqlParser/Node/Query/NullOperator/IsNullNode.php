<?php

namespace AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\NullOperator;

use AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\AbstractNullOperatorNode;

/**
 * Class IsNullNode
 *
 * @package AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\NullOperator
 */
class IsNullNode extends AbstractNullOperatorNode
{
    /**
     * @inheritdoc
     */
    public function getNodeName(): string
    {
        return 'isnull';
    }
}
