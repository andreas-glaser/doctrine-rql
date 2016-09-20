<?php
namespace AndreasGlaser\DoctrineRql\Extension\Xiag\Node\Query;

/**
 * Class IsNullNode
 *
 * @package AndreasGlaser\DoctrineRql\Extension\Xiag\Node
 * @author  Andreas Glaser
 */
class IsNullNode extends AbstractNullComparisonOperatorNode
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
