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
     * IsNullNode constructor.
     *
     * @param $field
     */
    public function __construct($field)
    {
        $this->setField($field);
    }

    /**
     * @inheritdoc
     */
    public function getNodeName()
    {
        return 'isnull';
    }
}
