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
        $rql = sprintf('and(gt(cart.id,2),eq(cart.products.name,%s))', $productName); // user input

        ORMVisitorFactory::appendFiltersOnly($qbTest1, $rql, false);

        $this->assertEquals('SELECT c0_.id AS id_0, c0_.deleted_at AS deleted_at_1, p1_.id AS id_2, p1_.name AS name_3, p1_.published_at AS published_at_4, p1_.deleted_at AS deleted_at_5, p2_.id AS id_6, p3_.id AS id_7, p3_.name AS name_8, p2_.product_id AS product_id_9, p2_.photo_id AS photo_id_10 FROM Cart c0_ LEFT JOIN cart_products c4_ ON c0_.id = c4_.cart_id LEFT JOIN Product p1_ ON p1_.id = c4_.product_id LEFT JOIN ProductPhoto p2_ ON p1_.id = p2_.product_id LEFT JOIN Photo p3_ ON p2_.photo_id = p3_.id WHERE c0_.id > ? AND p1_.name = ?', $qbTest1->getQuery()->getSQL());

        $qbTest2 = clone $qb;

        $rql = '(eq(cart.id,string:1)|lt(cart.id,2)|(cart.id<>3&cart.id>=string:4&cart.id=eq=boolean:1))&cart.id!=5&not(or(cart.id=6,eq(cart.id,2)))&cart.id=1&(cart.id==2|cart.id<-3|in(cart.id,(2,float:3)))&sort(+cart.id,-cart.id)&limit(1,2)';

        ORMVisitorFactory::appendFiltersOnly($qbTest2, $rql, false);

        $this->assertEquals('SELECT c0_.id AS id_0, c0_.deleted_at AS deleted_at_1, p1_.id AS id_2, p1_.name AS name_3, p1_.published_at AS published_at_4, p1_.deleted_at AS deleted_at_5, p2_.id AS id_6, p3_.id AS id_7, p3_.name AS name_8, p2_.product_id AS product_id_9, p2_.photo_id AS photo_id_10 FROM Cart c0_ LEFT JOIN cart_products c4_ ON c0_.id = c4_.cart_id LEFT JOIN Product p1_ ON p1_.id = c4_.product_id LEFT JOIN ProductPhoto p2_ ON p1_.id = p2_.product_id LEFT JOIN Photo p3_ ON p2_.photo_id = p3_.id WHERE (c0_.id = ? OR c0_.id < ? OR (c0_.id <> ? AND c0_.id >= ? AND c0_.id = ?)) AND c0_.id <> ? AND (NOT (c0_.id = ? OR c0_.id = ?)) AND c0_.id = ? AND (c0_.id = ? OR c0_.id < ? OR c0_.id IN (2, 3)) ORDER BY c0_.id DESC LIMIT 1 OFFSET 2', $qbTest2->getQuery()->getSQL());
    }

    /**
     * @author Andreas Glaser
     * @group  this
     */
    public function testIsNullNode()
    {
        $qb = $this->em->createQueryBuilder();

        $qb
            ->select('cart, products, photos, photo')
            ->from('AndreasGlaser\DoctrineRql\Entity\Cart', 'cart')
            ->leftJoin('cart.products', 'products')
            ->leftJoin('products.photos', 'photos')
            ->leftJoin('photos.photo', 'photo');

        $rql = 'isnull(cart.products.id)'; // user input

        ORMVisitorFactory::appendFiltersOnly($qb, $rql, false);

        $query = $qb->getQuery();

        $this->assertEquals('SELECT c0_.id AS id_0, c0_.deleted_at AS deleted_at_1, p1_.id AS id_2, p1_.name AS name_3, p1_.published_at AS published_at_4, p1_.deleted_at AS deleted_at_5, p2_.id AS id_6, p3_.id AS id_7, p3_.name AS name_8, p2_.product_id AS product_id_9, p2_.photo_id AS photo_id_10 FROM Cart c0_ LEFT JOIN cart_products c4_ ON c0_.id = c4_.cart_id LEFT JOIN Product p1_ ON p1_.id = c4_.product_id LEFT JOIN ProductPhoto p2_ ON p1_.id = p2_.product_id LEFT JOIN Photo p3_ ON p2_.photo_id = p3_.id WHERE p1_.id IS NULL', $query->getSQL());
        $this->assertEquals(0, $query->getParameters()->count());
    }

    /**
     * @author Andreas Glaser
     * @group  this
     */
    public function testIsNotNullNode()
    {
        $qb = $this->em->createQueryBuilder();

        $qb
            ->select('cart, products, photos, photo')
            ->from('AndreasGlaser\DoctrineRql\Entity\Cart', 'cart')
            ->leftJoin('cart.products', 'products')
            ->leftJoin('products.photos', 'photos')
            ->leftJoin('photos.photo', 'photo');

        $rql = 'isnotnull(cart.products.id)'; // user input

        ORMVisitorFactory::appendFiltersOnly($qb, $rql, false);

        $query = $qb->getQuery();

        $this->assertEquals('SELECT c0_.id AS id_0, c0_.deleted_at AS deleted_at_1, p1_.id AS id_2, p1_.name AS name_3, p1_.published_at AS published_at_4, p1_.deleted_at AS deleted_at_5, p2_.id AS id_6, p3_.id AS id_7, p3_.name AS name_8, p2_.product_id AS product_id_9, p2_.photo_id AS photo_id_10 FROM Cart c0_ LEFT JOIN cart_products c4_ ON c0_.id = c4_.cart_id LEFT JOIN Product p1_ ON p1_.id = c4_.product_id LEFT JOIN ProductPhoto p2_ ON p1_.id = p2_.product_id LEFT JOIN Photo p3_ ON p2_.photo_id = p3_.id WHERE p1_.id IS NOT NULL', $query->getSQL());
        $this->assertEquals(0, $query->getParameters()->count());
    }

    /**
     * @author Andreas Glaser
     * @group  this
     */
    public function testIsNullNodeComplex()
    {
        $qb = $this->em->createQueryBuilder();

        $qb
            ->select('cart, products, photos, photo')
            ->from('AndreasGlaser\DoctrineRql\Entity\Cart', 'cart')
            ->leftJoin('cart.products', 'products')
            ->leftJoin('products.photos', 'photos')
            ->leftJoin('photos.photo', 'photo');

        $rql = 'and(isnull(cart.deletedAt),isnotnull(cart.products.publishedAt),gt(cart.products.publishedAt,2015-06-02),or(isnull(cart.products.id),gt(cart.products.id,1000)))'; // user input

        ORMVisitorFactory::appendFiltersOnly($qb, $rql, false);

        $query = $qb->getQuery();

        $this->assertEquals('SELECT c0_.id AS id_0, c0_.deleted_at AS deleted_at_1, p1_.id AS id_2, p1_.name AS name_3, p1_.published_at AS published_at_4, p1_.deleted_at AS deleted_at_5, p2_.id AS id_6, p3_.id AS id_7, p3_.name AS name_8, p2_.product_id AS product_id_9, p2_.photo_id AS photo_id_10 FROM Cart c0_ LEFT JOIN cart_products c4_ ON c0_.id = c4_.cart_id LEFT JOIN Product p1_ ON p1_.id = c4_.product_id LEFT JOIN ProductPhoto p2_ ON p1_.id = p2_.product_id LEFT JOIN Photo p3_ ON p2_.photo_id = p3_.id WHERE c0_.deleted_at IS NULL AND p1_.published_at IS NOT NULL AND p1_.published_at > ? AND (p1_.id IS NULL OR p1_.id > ?)', $query->getSQL());
        $this->assertEquals(2, $query->getParameters()->count());
    }
}