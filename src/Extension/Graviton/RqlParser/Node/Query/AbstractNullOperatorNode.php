<?php

namespace AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query;

use Graviton\RqlParser\Node\Query\AbstractComparisonOperatorNode;
use Graviton\RqlParser\RqlEncoder;

/**
 * Class AbstractNullOperatorNode
 *
 * @package AndreasGlaser\DoctrineRql\Extension\Graviton\RqlParser\Node\Query
 */
abstract class AbstractNullOperatorNode extends AbstractComparisonOperatorNode
{
    /**
     * AbstractNullOperatorNode constructor.
     *
     * @param string $field
     */
    public function __construct(string $field)
    {
        $this->setField($field);
    }

    /**
     * @inheritdoc
     */
    public function toRql(): string
    {
        return sprintf(
            '%s(%s)',
            $this->getNodeName(),
            RqlEncoder::encodeFieldName($this->getField())
        );
    }
}
