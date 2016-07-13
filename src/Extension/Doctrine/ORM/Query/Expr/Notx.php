<?php

namespace AndreasGlaser\DoctrineRql\Extension\Doctrine\ORM\Query\Expr;

use Doctrine\ORM\Query\Expr\Composite;

/**
 * Class Notx
 *
 * @package AndreasGlaser\DoctrineRql\Extension\Doctrine\ORM\Query\Expr
 * @author  Andreas Glaser
 */
class Notx extends Composite
{
    /**
     * @var string
     */
    protected $separator = ' NOT ';

    /**
     * @var array
     */
    protected $allowedClasses = [
        'Doctrine\ORM\Query\Expr\Comparison',
        'Doctrine\ORM\Query\Expr\Func',
        'Doctrine\ORM\Query\Expr\Orx',
        'Doctrine\ORM\Query\Expr\Andx',
    ];

    /**
     * @return array
     */
    public function getParts()
    {
        return $this->parts;
    }
}
