<?php

namespace AndreasGlaser\DoctrineRql\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Product
 *
 * @package AndreasGlaser\DoctrineRql\Entity
 * @author  Andreas Glaser
 *
 * @ORM\Entity()
 * @ORM\Table()
 */
class Product
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    public $name;

    /**
     * @ORM\OneToMany(targetEntity="ProductPhoto", mappedBy="product")
     */
    public $photos;

    /**
     * @ORM\Column(name="published_at", type="datetime", nullable=true)
     */
    public $publishedAt;

    /**
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    public $deletedAt;

    public function __construct()
    {
        $this->photos = new ArrayCollection();
    }
}
