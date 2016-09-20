<?php

namespace AndreasGlaser\DoctrineRql\Extension\Xiag\TokenParser\Query\Basic;

use AndreasGlaser\DoctrineRql\Extension\Xiag\Node\Query\IsNullNode;

/**
 * Class IsNullTokenParser
 *
 * @package AndreasGlaser\DoctrineRql\Extension\Xiag\TokenParser\Query\Basic
 * @author  Andreas Glaser
 */
class IsNullTokenParser extends AbstractNullTokenParser
{
    /**
     * @inheritdoc
     */
    protected function getOperatorName()
    {
        return 'isnull';
    }

    /**
     * @param $field
     *
     * @return \AndreasGlaser\DoctrineRql\Extension\Xiag\Node\Query\IsNullNode
     * @author Andreas Glaser
     */
    protected function createNode($field)
    {
        return new IsNullNode($field);
    }
}
