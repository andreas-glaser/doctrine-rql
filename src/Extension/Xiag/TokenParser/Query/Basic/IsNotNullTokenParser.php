<?php

namespace AndreasGlaser\DoctrineRql\Extension\Xiag\TokenParser\Query\Basic;

use AndreasGlaser\DoctrineRql\Extension\Xiag\Node\Query\IsNotNullNode;

/**
 * Class IsNotNullTokenParser
 *
 * @package AndreasGlaser\DoctrineRql\Extension\Xiag\TokenParser\Query\Basic
 * @author  Andreas Glaser
 */
class IsNotNullTokenParser extends AbstractNullTokenParser
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
     * @return \AndreasGlaser\DoctrineRql\Extension\Xiag\Node\Query\IsNotNullNode
     * @author Andreas Glaser
     */
    protected function createNode($field)
    {
        return new IsNotNullNode($field);
    }
}
