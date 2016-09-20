<?php
namespace AndreasGlaser\DoctrineRql\Extension\Xiag\Node\Query;

use Xiag\Rql\Parser\Node\Query\AbstractComparisonOperatorNode;

/**
 * Class AbstractNullComparisonOperatorNode
 *
 * @package AndreasGlaser\DoctrineRql\Extension\Xiag\Node\Query
 * @author  Andreas Glaser
 */
abstract class AbstractNullComparisonOperatorNode extends AbstractComparisonOperatorNode
{
    /**
     * AbstractNullComparisonOperatorNode constructor.
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
