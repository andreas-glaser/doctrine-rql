<?php

namespace AndreasGlaser\DoctrineRql\Extension\Xiag\Rql\Parser\Node\Query\NullOperator;

use AndreasGlaser\DoctrineRql\Extension\Xiag\Rql\Parser\Node\Query\AbstractNullOperatorNode;

/**
 * Class IsNotNullNode
 *
 * @package AndreasGlaser\DoctrineRql\Extension\Xiag\Rql\Parser\Node\Query\NullOperator
 * @author  Andreas Glaser
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
