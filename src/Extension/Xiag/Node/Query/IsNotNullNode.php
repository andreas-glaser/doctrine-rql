<?php
namespace AndreasGlaser\DoctrineRql\Extension\Xiag\Node\Query;

/**
 * Class IsNotNullNode
 *
 * @package AndreasGlaser\DoctrineRql\Extension\Xiag\Node
 * @author  Andreas Glaser
 */
class IsNotNullNode extends AbstractNullComparisonOperatorNode
{
    /**
     * @inheritdoc
     */
    public function getNodeName()
    {
        return 'isnotnull';
    }
}
