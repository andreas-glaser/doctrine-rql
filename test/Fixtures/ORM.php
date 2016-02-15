<?php

namespace AndreasGlaser\DoctrineRql\Fixtures;

use AndreasGlaser\DoctrineRql\Entity;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class ORM
 *
 * @package AndreasGlaser\DoctrineRql\Fixtures
 * @author  Andreas Glaser
 */
class ORM implements FixtureInterface
{
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     *
     * @author Andreas Glaser
     */
    public function load(ObjectManager $manager)
    {
        $product = new Entity\Product();
        $product->name = 'Product 1';
        $manager->persist($product);

        $product = new Entity\Product();
        $product->name = 'Product 2';
        $manager->persist($product);

        $product = new Entity\Product();
        $product->name = 'Product 3';
        $manager->persist($product);

        $manager->flush();
    }
}

