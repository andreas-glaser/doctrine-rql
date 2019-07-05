<?php

namespace AndreasGlaser\DoctrineRql\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ProductPhoto
 *
 * @package AndreasGlaser\DoctrineRql\Entity
 * @ORM\Entity()
 * @ORM\Table()
 */
class ProductPhoto
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="photos")
     * @ORM\JoinColumn(name="product_id", nullable=false, referencedColumnName="id")
     */
    public $product;

    /**
     * @ORM\OneToOne(targetEntity="Photo", cascade={"all"})
     * @ORM\JoinColumn(name="photo_id", nullable=false, referencedColumnName="id")
     */
    public $photo;
}
