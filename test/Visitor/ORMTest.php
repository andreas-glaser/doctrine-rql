<?php

namespace AndreasGlaser\DoctrineRql\Visitor;

use AndreasGlaser\DoctrineRql\Factory\ORMVisitorFactory;
use AndreasGlaser\DoctrineRql\Fixtures;
use AndreasGlaser\DoctrineRql\Helper;
use AndreasGlaser\DoctrineRql\Visitor as Visitor;
use Doctrine\Common\DataFixtures;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM;
use Doctrine\ORM\EntityManager;

/**
 * Class ORMVisitor
 *
 * @package AndreasGlaser\DoctrineRql
 * @author  Andreas Glaser
 */
class ORMTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\Tools\ToolsException
     * @author Andreas Glaser
     */
    protected function setUp()
    {
        $conn = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'dbname' => ':memory:',
            'memory' => true,
        ]);

        $config = ORM\Tools\Setup::createAnnotationMetadataConfiguration([__DIR__ . '/../Entity'], true, null, null, false);
        $this->em = EntityManager::create($conn, $config);

        $schemaTools = new ORM\Tools\SchemaTool($this->em);
        $schemaTools->createSchema($this->em->getMetadataFactory()->getAllMetadata());

        $loader = new DataFixtures\Loader();
        $loader->addFixture(new Fixtures\ORM());

        $executor = new DataFixtures\Executor\ORMExecutor($this->em, new DataFixtures\Purger\ORMPurger());
        $executor->execute($loader->getFixtures());
    }

    /**
     * @author Andreas Glaser
     */
    public function test()
    {
        $qb = $this->em->createQueryBuilder();

        $qb
            ->select('cart, products, photos, photo')
            ->from('AndreasGlaser\DoctrineRql\Entity\Cart', 'cart')
            ->leftJoin('cart.products', 'products')
            ->leftJoin('products.photos', 'photos')
            ->leftJoin('photos.photo', 'photo');

        $qbTest1 = clone $qb;

        $productName = Helper\StringEncode::encode('Powered Wheelchair X200');
        $rql = sprintf('and(gt(cart.id,2),eq(cart.products.name,%s))', $productName); // user input

        ORMVisitorFactory::appendFiltersOnly($qbTest1, $rql, false);

        $this->assertEquals('SELECT c0_.id AS id_0, p1_.id AS id_1, p1_.name AS name_2, p2_.id AS id_3, p3_.id AS id_4, p3_.name AS name_5, p2_.product_id AS product_id_6, p2_.photo_id AS photo_id_7 FROM Cart c0_ LEFT JOIN cart_products c4_ ON c0_.id = c4_.cart_id LEFT JOIN Product p1_ ON p1_.id = c4_.product_id LEFT JOIN ProductPhoto p2_ ON p1_.id = p2_.product_id LEFT JOIN Photo p3_ ON p2_.photo_id = p3_.id WHERE c0_.id > ? AND p1_.name = ?', $qbTest1->getQuery()->getSQL());

        $qbTest2 = clone $qb;

        $rql = '(eq(cart.id,string:1)|lt(cart.id,2)|(cart.id<>3&cart.id>=string:4&cart.id=eq=boolean:1))&cart.id!=5&not(or(cart.id=6,eq(cart.id,2)))&cart.id=1&(cart.id==2|cart.id<-3|in(cart.id,(2,float:3)))&sort(+cart.id,-cart.id)&limit(1,2)';

        ORMVisitorFactory::appendFiltersOnly($qbTest2, $rql, false);

        $this->assertEquals('SELECT c0_.id AS id_0, p1_.id AS id_1, p1_.name AS name_2, p2_.id AS id_3, p3_.id AS id_4, p3_.name AS name_5, p2_.product_id AS product_id_6, p2_.photo_id AS photo_id_7 FROM Cart c0_ LEFT JOIN cart_products c4_ ON c0_.id = c4_.cart_id LEFT JOIN Product p1_ ON p1_.id = c4_.product_id LEFT JOIN ProductPhoto p2_ ON p1_.id = p2_.product_id LEFT JOIN Photo p3_ ON p2_.photo_id = p3_.id WHERE (c0_.id = ? OR c0_.id < ? OR (c0_.id <> ? AND c0_.id >= ? AND c0_.id = ?)) AND c0_.id <> ? AND (NOT (c0_.id = ? OR c0_.id = ?)) AND c0_.id = ? AND (c0_.id = ? OR c0_.id < ? OR c0_.id IN (2, 3)) ORDER BY c0_.id DESC LIMIT 1 OFFSET 2', $qbTest2->getQuery()->getSQL());
    }
}