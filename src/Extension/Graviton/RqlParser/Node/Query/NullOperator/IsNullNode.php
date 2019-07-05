<?php

namespace AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\NullOperator;

use AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\AbstractNullOperatorNode;

/**
 * Class IsNullNode
 *
 * @package AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query\NullOperator
 * @author  Andreas Glaser
 */
class IsNullNode extends AbstractNullOperatorNode
{

    /**
     * IsNullNode constructor.
     *
     * @param $field
     *
     * @author Andreas Glaser
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
