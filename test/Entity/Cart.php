<?php

namespace AndreasGlaser\DoctrineRql\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Cart
 *
 * @package AndreasGlaser\DoctrineRql\Entity
 * @author  Andreas Glaser
 *
 * @ORM\Entity()
 * @ORM\Table()
 */
class Cart
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    public $deletedAt;

    /**
     * Product[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Product")
     * @ORM\JoinTable(name="cart_products",
     *      joinColumns={@ORM\JoinColumn(name="cart_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")}
     * )
     */
    public $products;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }
}
