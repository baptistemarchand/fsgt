<?php
declare(strict_types=1);

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Club
 *
 * @ORM\Entity
 * @ORM\Table(name="club")
 */
class Club
{
    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="main_club")
     */
    public $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getUsersByState(array $states)
    {
        $criteria = Criteria::create()->where(
            Criteria::expr()->in('marking', $states)
        );
        return $this->users->matching($criteria);
    }

    public function getUsersNeedingLicenses()
    {
        $criteria = Criteria::create()->where(
            Criteria::expr()->eq('needs_license', true)
        );
        return $this->users->matching($criteria);
    }

    public function getUserRepartition($workflow)
    {
        $places = $workflow->getDefinition()->getPlaces();
        $repartition = array_fill_keys($places, 0);

        foreach ($this->users as $user)
            $repartition[$user->getState()] += 1;

        return $repartition;
    }

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

    /**
     * @var float
     *
     * @ORM\Column(name="percentage_of_experienced", type="float")
     */
    public $percentageOfExperienced = 0.5;
}
