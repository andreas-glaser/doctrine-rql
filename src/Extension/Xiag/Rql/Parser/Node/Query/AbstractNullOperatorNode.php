<?php

namespace AndreasGlaser\DoctrineRql\Extension\Xiag\Rql\Parser\Node\Query;

use Xiag\Rql\Parser\Node\Query\AbstractComparisonOperatorNode;

/**
 * Class AbstractNullOperatorNode
 *
 * @package AndreasGlaser\DoctrineRql\Extension\Xiag\Rql\Parser\Node\Query
 * @author  Andreas Glaser
 */
abstract class AbstractNullOperatorNode extends AbstractComparisonOperatorNode
{
    /**
     * AbstractNullOperatorNode constructor.
     *
     * @param $field
     *
     * @author Andreas Glaser
     */
    public function __construct($field)
    {
        $this->setField($field);
    }
}
