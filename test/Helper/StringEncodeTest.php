<?php

namespace AndreasGlaser\DoctrineRql\Visitor;

use AndreasGlaser\DoctrineRql\Helper\StringEncode;
use PHPUnit\Framework\TestCase;

/**
 * Class StringEncodeTest
 *
 * @package AndreasGlaser\DoctrineRql\Visitor
 * @author  Andreas Glaser
 */
class StringEncodeTest extends TestCase
{
    /**
     * @author Andreas Glaser
     */
    public function testEncode()
    {
        $this->assertEquals('2016%2D01%2D01%2023%3A21%3A20', StringEncode::encode('2016-01-01 23:21:20'));
        $this->assertEquals('3%2E32', StringEncode::encode('3.32'));
        $this->assertEquals('3%2C32', StringEncode::encode('3,32'));
        $this->assertEquals('hello%20there', StringEncode::encode('hello there'));
    }
}