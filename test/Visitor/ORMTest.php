<?php

namespace AndreasGlaser\DoctrineRql\Visitor;

use AndreasGlaser\DoctrineRql\Entity\Cart;
use AndreasGlaser\DoctrineRql\Entity\Product;
use AndreasGlaser\DoctrineRql\Factory\ORMVisitorFactory;
use AndreasGlaser\DoctrineRql\Fixtures;
use AndreasGlaser\DoctrineRql\Helper;
use Doctrine\Common\DataFixtures;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;

/**
 * Class ORMVisitor
 *
 * @package AndreasGlaser\DoctrineRql
 */
class ORMTest extends TestCase
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @throws ORM\ORMException
     * @throws ORM\Tools\ToolsException
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function setUp(): void
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

    public function test(): void
    {
        $qb = $this->em->createQueryBuilder();

        $qb
            ->select('cart, products, photos, photo')
            ->from(Cart::class, 'cart')
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

        $this->assertEquals('SELECT c0_.id AS id_0, c0_.deleted_at AS deleted_at_1, p1_.id AS id_2, p1_.name AS name_3, p1_.published_at AS published_at_4, p1_.deleted_at AS deleted_at_5, p2_.id AS id_6, p3_.id AS id_7, p3_.name AS name_8, p2_.product_id AS product_id_9, p2_.photo_id AS photo_id_10 FROM Cart c0_ LEFT JOIN cart_products c4_ ON c0_.id = c4_.cart_id LEFT JOIN Product p1_ ON p1_.id = c4_.product_id LEFT JOIN ProductPhoto p2_ ON p1_.id = p2_.product_id LEFT JOIN Photo p3_ ON p2_.photo_id = p3_.id WHERE (c0_.id = ? OR c0_.id < ? OR (c0_.id <> ? AND c0_.id >= ? AND c0_.id = ?)) AND c0_.id <> ? AND (NOT (c0_.id = ? OR c0_.id = ?)) AND c0_.id = ? AND (c0_.id = ? OR c0_.id < ? OR c0_.id IN (?, ?)) ORDER BY c0_.id DESC LIMIT 1 OFFSET 2', $qbTest2->getQuery()->getSQL());
    }

    public function testInOperator(): void
    {
        $qb = $this->em->createQueryBuilder()
            ->select('products')
            ->from(Product::class, 'products');

        $productNames = [
            Helper\StringEncode::encode('Product 1'),
            Helper\StringEncode::encode('Product 5'),
        ];
        $rql = sprintf('in(name,(%s))', implode(',', $productNames));

        ORMVisitorFactory::appendFiltersOnly($qb, $rql);

        $this->assertSame('SELECT p0_.id AS id_0, p0_.name AS name_1, p0_.published_at AS published_at_2, p0_.deleted_at AS deleted_at_3 FROM Product p0_ WHERE p0_.name IN (?, ?)', $qb->getQuery()->getSQL());

        $expectedNames = [
            'Product 1',
            'Product 5',
        ];
        $actualNames = array_map(function (Product $product) {
            return $product->name;
        }, $qb->getQuery()->getResult());
        $this->assertEquals($expectedNames, $actualNames);
    }

    public function testOutOperator(): void
    {
        $qb = $this->em->createQueryBuilder()
            ->select('products')
            ->from(Product::class, 'products');

        $productNames = [
            Helper\StringEncode::encode('Product 1'),
            Helper\StringEncode::encode('Product 5'),
        ];
        $rql = sprintf('out(name,(%s))', implode(',', $productNames));

        ORMVisitorFactory::appendFiltersOnly($qb, $rql);

        $this->assertSame('SELECT p0_.id AS id_0, p0_.name AS name_1, p0_.published_at AS published_at_2, p0_.deleted_at AS deleted_at_3 FROM Product p0_ WHERE p0_.name NOT IN (?, ?)', $qb->getQuery()->getSQL());

        $expectedNames = [
            'Product 2',
            'Product 3',
            'Product 4',
            'Product 6',
            'Product 7',
            'Product 8',
            'Product 9',
            'Product 10',
        ];
        $actualNames = array_map(function (Product $product) {
            return $product->name;
        }, $qb->getQuery()->getResult());
        $this->assertEquals($expectedNames, $actualNames);
    }

    public function testLike01(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('product')
            ->from(Product::class, 'product');

        $qbTest = clone $qb;

        $productName = Helper\StringEncode::encode('Product ') . '?';
        $rql = sprintf('like(product.name,%s)', $productName); // user input

        ORMVisitorFactory::appendFiltersOnly($qbTest, $rql, false);

        $this->assertEquals("SELECT p0_.id AS id_0, p0_.name AS name_1, p0_.published_at AS published_at_2, p0_.deleted_at AS deleted_at_3 FROM Product p0_ WHERE p0_.name LIKE ? ESCAPE '\'", $qbTest->getQuery()->getSQL());
        $parameters = $qbTest->getQuery()->getParameters();
        $this->assertSame(1, $parameters->count());
        $parameter = $parameters[0];
        $this->assertEquals('Product _', $parameter->getValue());
        $this->assertCount(9, $qbTest->getQuery()->getResult());
    }


    public function testLike02(): void
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('product')
            ->from(Product::class, 'product');

        $qbTest = clone $qb;

        $productName = '*' . Helper\StringEncode::encode('a%b_c\\d?e*f') . '?';
        $rql = sprintf('like(product.name,%s)', $productName); // user input

        ORMVisitorFactory::appendFiltersOnly($qbTest, $rql, false);

        $this->assertEquals("SELECT p0_.id AS id_0, p0_.name AS name_1, p0_.published_at AS published_at_2, p0_.deleted_at AS deleted_at_3 FROM Product p0_ WHERE p0_.name LIKE ? ESCAPE '\'", $qbTest->getQuery()->getSQL());
        $parameters = $qbTest->getQuery()->getParameters();
        $this->assertSame(1, $parameters->count());
        $parameter = $parameters[0];
        $this->assertEquals('%a\%b\_c\\\\d?e*f_', $parameter->getValue());
        $this->assertCount(0, $qbTest->getQuery()->getResult());
    }


    public function testIsNullNode(): void
    {
        $qb = $this->em->createQueryBuilder();

        $qb
            ->select('cart, products, photos, photo')
            ->from(Cart::class, 'cart')
            ->leftJoin('cart.products', 'products')
            ->leftJoin('products.photos', 'photos')
            ->leftJoin('photos.photo', 'photo');

        $fieldName = Helper\StringEncode::encode('cart.products.id');
        $rql = 'isnull(' . $fieldName . ')';

        ORMVisitorFactory::appendFiltersOnly($qb, $rql, false);

        $query = $qb->getQuery();

        $this->assertEquals('SELECT c0_.id AS id_0, c0_.deleted_at AS deleted_at_1, p1_.id AS id_2, p1_.name AS name_3, p1_.published_at AS published_at_4, p1_.deleted_at AS deleted_at_5, p2_.id AS id_6, p3_.id AS id_7, p3_.name AS name_8, p2_.product_id AS product_id_9, p2_.photo_id AS photo_id_10 FROM Cart c0_ LEFT JOIN cart_products c4_ ON c0_.id = c4_.cart_id LEFT JOIN Product p1_ ON p1_.id = c4_.product_id LEFT JOIN ProductPhoto p2_ ON p1_.id = p2_.product_id LEFT JOIN Photo p3_ ON p2_.photo_id = p3_.id WHERE p1_.id IS NULL', $query->getSQL());
        $this->assertEquals(0, $query->getParameters()->count());
    }


    public function testIsNotNullNode(): void
    {
        $qb = $this->em->createQueryBuilder();

        $qb
            ->select('cart, products, photos, photo')
            ->from(Cart::class, 'cart')
            ->leftJoin('cart.products', 'products')
            ->leftJoin('products.photos', 'photos')
            ->leftJoin('photos.photo', 'photo');

        $fieldName = Helper\StringEncode::encode('cart.products.id');
        $rql = 'isnotnull(' . $fieldName . ')';

        ORMVisitorFactory::appendFiltersOnly($qb, $rql, false);

        $query = $qb->getQuery();

        $this->assertEquals('SELECT c0_.id AS id_0, c0_.deleted_at AS deleted_at_1, p1_.id AS id_2, p1_.name AS name_3, p1_.published_at AS published_at_4, p1_.deleted_at AS deleted_at_5, p2_.id AS id_6, p3_.id AS id_7, p3_.name AS name_8, p2_.product_id AS product_id_9, p2_.photo_id AS photo_id_10 FROM Cart c0_ LEFT JOIN cart_products c4_ ON c0_.id = c4_.cart_id LEFT JOIN Product p1_ ON p1_.id = c4_.product_id LEFT JOIN ProductPhoto p2_ ON p1_.id = p2_.product_id LEFT JOIN Photo p3_ ON p2_.photo_id = p3_.id WHERE p1_.id IS NOT NULL', $query->getSQL());
        $this->assertEquals(0, $query->getParameters()->count());
    }


    public function testIsNullNodeComplex(): void
    {
        $qb = $this->em->createQueryBuilder();

        $qb
            ->select('cart, products, photos, photo')
            ->from(Cart::class, 'cart')
            ->leftJoin('cart.products', 'products')
            ->leftJoin('products.photos', 'photos')
            ->leftJoin('photos.photo', 'photo');

        $rql = 'and(isnull(cart.deletedAt),isnotnull(cart.products.publishedAt),gt(cart.products.publishedAt,2015%2D06%2D02),or(isnull(cart.products.id),gt(cart.products.id,1000)))';

        ORMVisitorFactory::appendFiltersOnly($qb, $rql, false);

        $query = $qb->getQuery();

        $this->assertEquals('SELECT c0_.id AS id_0, c0_.deleted_at AS deleted_at_1, p1_.id AS id_2, p1_.name AS name_3, p1_.published_at AS published_at_4, p1_.deleted_at AS deleted_at_5, p2_.id AS id_6, p3_.id AS id_7, p3_.name AS name_8, p2_.product_id AS product_id_9, p2_.photo_id AS photo_id_10 FROM Cart c0_ LEFT JOIN cart_products c4_ ON c0_.id = c4_.cart_id LEFT JOIN Product p1_ ON p1_.id = c4_.product_id LEFT JOIN ProductPhoto p2_ ON p1_.id = p2_.product_id LEFT JOIN Photo p3_ ON p2_.photo_id = p3_.id WHERE c0_.deleted_at IS NULL AND p1_.published_at IS NOT NULL AND p1_.published_at > ? AND (p1_.id IS NULL OR p1_.id > ?)', $query->getSQL());
        $this->assertEquals(2, $query->getParameters()->count());
    }

    public function testSort(): void
    {
        $qb = $this->em->createQueryBuilder()
            ->select('products')
            ->from(Product::class, 'products');
        $rql = 'sort(+name,-publishedAt)';
        ORMVisitorFactory::appendFiltersOnly($qb, $rql);
        $this->assertSame('SELECT p0_.id AS id_0, p0_.name AS name_1, p0_.published_at AS published_at_2, p0_.deleted_at AS deleted_at_3 FROM Product p0_ ORDER BY p0_.name ASC, p0_.published_at DESC', $qb->getQuery()->getSQL());
    }
}
