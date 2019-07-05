<?php

namespace AndreasGlaser\DoctrineRql\Extension\Doctrine\ORM\Query;

use Doctrine\ORM\Query\Expr;

/**
 * This class is used to generate DQL expressions.
 *
 * @package AndreasGlaser\DoctrineRql\Extension\Doctrine\ORM\Query
 */
class ExpressionBuilder extends Expr
{
    /**
     * @param string $x
     * @param mixed  $y
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison
     */
    public function like($x, $y)
    {
        return parent::like($x, $y . " ESCAPE '\\'");
    }

    /**
     * @param string $x
     * @param mixed  $y
     *
     * @return \Doctrine\ORM\Query\Expr\Comparison
     */
    public function notLike($x, $y)
    {
        return parent::notLike($x, $y . " ESCAPE '\\'");
    }
}