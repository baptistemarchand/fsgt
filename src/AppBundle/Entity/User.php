<?php
namespace AppBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 * @Vich\Uploadable
 */
class User extends BaseUser
{
    /**
     * @Vich\UploadableField(mapping="medical_certificate", fileNameProperty="medicalCertificateName")
     * @var File
     */
    private $medicalCertificateFile;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $medicalCertificateName;
    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $updatedAt;
    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the  update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile $medicalCertificate
     *
     * @return Product
     */
    public function setMedicalCertificateFile(File $medicalCertificate = null)
    {
        $this->medicalCertificateFile = $medicalCertificate;

        if ($medicalCertificate)
        {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }
    /**
     * @return File|null
     */
    public function getMedicalCertificateFile()
    {
        return $this->medicalCertificateFile;
    }
    /**
     * @param string $medicalCertificateName
     *
     * @return Product
     */
    public function setMedicalCertificateName($medicalCertificateName)
    {
        $this->medicalCertificateName = $medicalCertificateName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getMedicalCertificateName()
    {
        return $this->medicalCertificateName;
    }




    
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(name="status", type="string", length=255)
     */
    public $status = 'new';
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $stripe_charge_id;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $first_name;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $last_name;
    /**
     * @ORM\Column(type="date", length=255, nullable=true)
     */
    public $birthday;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $gender;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $address;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $zip_code;    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $phone_number;
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    public $licence_id;
    /**
     * @ORM\Column(type="boolean", length=255, nullable=true)
     */
    public $does_not_need_training = false;
    /**
     * @ORM\Column(type="boolean", length=255, nullable=true)
     */
    public $skill_checked = false;

    public function setEmail($email)
    {
        $this->setUsername($email);

        return parent::setEmail($email);
    }
    
    public function __construct()
    {
        parent::__construct();
    }
}