<?php

namespace AppBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * Slide
 *
 * @ORM\Table(name="slide")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class Slide implements Translatable
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="content", type="string", length=255)
     */
    private $content;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @var string
     *
     * @ORM\Column(name="header_type", type="string", length=2)
     */
    private $headerType;

    /**
     * @ORM\Column(type="string", length=4)
     */
    protected $ext;


    protected $file;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Slide
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Slide
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return Slide
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set headerType
     *
     * @param string $headerType
     * @return Slide
     */
    public function setHeaderType($headerType)
    {
        $this->headerType = $headerType;

        return $this;
    }

    /**
     * Get headerType
     *
     * @return string 
     */
    public function getHeaderType()
    {
        return $this->headerType;
    }

    /**
     * Set ext
     *
     * @param string $ext
     * @return File
     */
    public function setExt($ext)
    {
        $this->ext = $ext;

        return $this;
    }

    /**
     * Get ext
     *
     * @return integer
     */
    public function getExt()
    {
        return $this->ext;
    }

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    public function preUpload()
    {
        if (null !== $this->file) {
            $this->ext = $this->file->guessExtension();
        }
    }

    public function upload()
    {
        // the file property can be empty if the field is not required
        if (null === $this->file) {
            return;
        }

        // use the original file name here but you should
        // sanitize it at least to avoid any security issues

        // move takes the target directory and then the
        // target filename to move to
        $this->file->move(
            $this->getUploadRootDir(),
            $this->id . '.' .$this->ext
        );

        // set the ext property to the filename where you've saved the file
        //$this->ext = $this->file->getClientOriginalName();

        // clean up the file property as you won't need it anymore
        $this->file = null;
    }

    public function getUploadRootDir()
    {
        return $_SERVER['DOCUMENT_ROOT'] . $this->getUploadDir();
    }

    public function getUploadDir()
    {
        return '/media/slides/';
    }

    public function getFileWebPath()
    {
        return $this->getUploadDir() . $this->id . '.' . $this->ext;
    }

    public function getFileAbsolutePath()
    {
        return $_SERVER['DOCUMENT_ROOT'] . $this->getUploadDir() . $this->id . '.' . $this->ext;
    }


}
