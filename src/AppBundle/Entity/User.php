<?php
// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255)
     */
    public $status = 'new';

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Assert\NotBlank(message="Please enter your name.", groups={"Profile"})
     */
    public $name = '';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     */
    public $stripe_charge_id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     */
    public $first_name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     */
    public $last_name;

    /**
     * @ORM\Column(type="date", length=255, nullable=true)
     *
     */
    public $birthday;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     */
    public $gender;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     */
    public $address;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     */
    public $zip_code;    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     */
    public $phone_number;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     */
    public $licence_id;
        
    public function __construct()
    {
        parent::__construct();
    }
}