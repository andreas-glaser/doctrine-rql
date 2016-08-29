<?php
namespace AndreasGlaser\DoctrineRql\Extension\Doctrine\ORM\Query\Expr;

use Doctrine\ORM\Query\Expr;

/**
 * This class is used to generate DQL expressions.
 *
 * @package AndreasGlaser\DoctrineRql\Extension\Doctrine\ORM\Query\Expr
 * @author Dominic Tubach <dominic.tubach@to.com>
 */
class ExpressionBuilder extends Expr
{
    public function like($x, $y)
    {
        return parent::like($x, $y . " ESCAPE '\\'");
    }

    public function notLike($x, $y)
    {
        return parent::notLike($x, $y . " ESCAPE '\\'");
    }
}