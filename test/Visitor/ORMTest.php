<?php

namespace AndreasGlaser\DoctrineRql\Visitor;

use AndreasGlaser\DoctrineRql\Factory\ORMVisitorFactory;
use AndreasGlaser\DoctrineRql\Fixtures;
use AndreasGlaser\DoctrineRql\Helper;
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
        $rql = sprintf('and(gt(cart%%2Eid,2),eq(cart%%2Eproducts%%2Ename,%s))', $productName); // user input

        ORMVisitorFactory::appendFiltersOnly($qbTest1, $rql, false);

        $this->assertEquals('SELECT c0_.id AS id_0, p1_.id AS id_1, p1_.name AS name_2, p2_.id AS id_3, p3_.id AS id_4, p3_.name AS name_5, p2_.product_id AS product_id_6, p2_.photo_id AS photo_id_7 FROM Cart c0_ LEFT JOIN cart_products c4_ ON c0_.id = c4_.cart_id LEFT JOIN Product p1_ ON p1_.id = c4_.product_id LEFT JOIN ProductPhoto p2_ ON p1_.id = p2_.product_id LEFT JOIN Photo p3_ ON p2_.photo_id = p3_.id WHERE c0_.id > ? AND p1_.name = ?', $qbTest1->getQuery()->getSQL());

        $qbTest2 = clone $qb;

        $rql = '(eq(cart%2Eid,string:1)|lt(cart%2Eid,2)|(cart%2Eid<>3&cart%2Eid>=string:4&cart%2Eid=eq=boolean:1))&cart%2Eid!=5&not(or(cart%2Eid=6,eq(cart%2Eid,2)))&cart%2Eid=1&(cart%2Eid==2|cart%2Eid<-3|in(cart%2Eid,(2,float:3)))&sort(+cart%2Eid,-cart%2Eid)&limit(1,2)';

        ORMVisitorFactory::appendFiltersOnly($qbTest2, $rql, false);

        $this->assertEquals('SELECT c0_.id AS id_0, p1_.id AS id_1, p1_.name AS name_2, p2_.id AS id_3, p3_.id AS id_4, p3_.name AS name_5, p2_.product_id AS product_id_6, p2_.photo_id AS photo_id_7 FROM Cart c0_ LEFT JOIN cart_products c4_ ON c0_.id = c4_.cart_id LEFT JOIN Product p1_ ON p1_.id = c4_.product_id LEFT JOIN ProductPhoto p2_ ON p1_.id = p2_.product_id LEFT JOIN Photo p3_ ON p2_.photo_id = p3_.id WHERE (c0_.id = ? OR c0_.id < ? OR (c0_.id <> ? AND c0_.id >= ? AND c0_.id = ?)) AND c0_.id <> ? AND (NOT (c0_.id = ? OR c0_.id = ?)) AND c0_.id = ? AND (c0_.id = ? OR c0_.id < ? OR c0_.id IN (2, 3)) ORDER BY c0_.id DESC LIMIT 1 OFFSET 2', $qbTest2->getQuery()->getSQL());
    }

    public function testLike01()
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('product')
            ->from('AndreasGlaser\DoctrineRql\Entity\Product', 'product');

        $qbTest = clone $qb;

        $productName = Helper\StringEncode::encode('Product ') . '?';
        $rql = sprintf('like(product%%2Ename,%s)', $productName); // user input

        ORMVisitorFactory::appendFiltersOnly($qbTest, $rql, false);

        $this->assertEquals('SELECT p0_.id AS id_0, p0_.name AS name_1 FROM Product p0_ WHERE p0_.name LIKE ? ESCAPE \'\\\'', $qbTest->getQuery()->getSQL());
        $parameters = $qbTest->getQuery()->getParameters();
        $this->assertSame(1, $parameters->count());
        $parameter = $parameters[0];
        $this->assertEquals('Product _', $parameter->getValue());
        $this->assertCount(9, $qbTest->getQuery()->getResult());
    }

    public function testLike02()
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('product')
            ->from('AndreasGlaser\DoctrineRql\Entity\Product', 'product');

        $qbTest = clone $qb;

        $productName = '*' . Helper\StringEncode::encode('a%b_c\\d?e*f') . '?';
        $rql = sprintf('like(product%%2Ename,%s)', $productName); // user input

        ORMVisitorFactory::appendFiltersOnly($qbTest, $rql, false);

        $this->assertEquals('SELECT p0_.id AS id_0, p0_.name AS name_1 FROM Product p0_ WHERE p0_.name LIKE ? ESCAPE \'\\\'', $qbTest->getQuery()->getSQL());
        $parameters = $qbTest->getQuery()->getParameters();
        $this->assertSame(1, $parameters->count());
        $parameter = $parameters[0];
        $this->assertEquals('%a\%b\_c\\\\d?e*f_', $parameter->getValue());
        $this->assertCount(0, $qbTest->getQuery()->getResult());
    }
}