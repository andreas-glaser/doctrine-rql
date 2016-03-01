<?php

namespace AndreasGlaser\DoctrineRql\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Photo
 *
 * @package AndreasGlaser\DoctrineRql\Entity
 * @author  Andreas Glaser
 *
 * @ORM\Entity()
 * @ORM\Table()
 */
class Photo
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @ORM\Column(name="name", type="text", nullable=false)
     */
    public $name;
}
