<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Club
 *
 * @ORM\Table(name="club")
 */
class Club
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    public $name;

    /**
     * @var string
     *
     * @ORM\Column(name="short_name", type="string", length=255, unique=true)
     */
    public $shortName;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255)
     */
    public $status;

    /**
     * @var int
     *
     * @ORM\Column(name="max_winners", type="integer")
     */
    public $maxWinners = 0;
}

