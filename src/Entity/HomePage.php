<?php

namespace AppBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;


/**
 * HomePage
 *
 * @ORM\Table(name="home_page")
 * @ORM\Entity
 */
class HomePage implements Translatable
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="block1", type="string", length=1000)
     */
    private $block1;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="block2", type="string", length=1000)
     */
    private $block2;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="block3", type="string", length=1000)
     */
    private $block3;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="block4", type="string", length=255)
     */
    private $block4;

    /**
     * @var string
     *
     * @ORM\Column(name="headerType", type="string", length=2)
     */
    private $headerType;

    /**
     * @ORM\ManyToOne(targetEntity="HomePage", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     **/
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="HomePage", mappedBy="parent", cascade={"remove"}, orphanRemoval=true)
     **/
    private $children;

    public function __construct() {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
    }


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
     * @return HomePage
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
     * Set block1
     *
     * @param string $block1
     * @return HomePage
     */
    public function setBlock1($block1)
    {
        $this->block1 = $block1;

        return $this;
    }

    /**
     * Get block1
     *
     * @return string 
     */
    public function getBlock1()
    {
        return $this->block1;
    }

    /**
     * Set block2
     *
     * @param string $block2
     * @return HomePage
     */
    public function setBlock2($block2)
    {
        $this->block2 = $block2;

        return $this;
    }

    /**
     * Get block2
     *
     * @return string 
     */
    public function getBlock2()
    {
        return $this->block2;
    }

    /**
     * Set block3
     *
     * @param string $block3
     * @return HomePage
     */
    public function setBlock3($block3)
    {
        $this->block3 = $block3;

        return $this;
    }

    /**
     * Get block3
     *
     * @return string 
     */
    public function getBlock3()
    {
        return $this->block3;
    }

    /**
     * Set block4
     *
     * @param string $block4
     * @return HomePage
     */
    public function setBlock4($block4)
    {
        $this->block4 = $block4;

        return $this;
    }

    /**
     * Get block4
     *
     * @return string 
     */
    public function getBlock4()
    {
        return $this->block4;
    }

    /**
     * Set headerType
     *
     * @param string $headerType
     * @return HomePage
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
     * Set parent
     *
     * @param \AppBundle\Entity\HomePage $parent
     *
     * @return HomePage
     */
    public function setParent(\AppBundle\Entity\HomePage $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return \AppBundle\Entity\HomePage
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add child
     *
     * @param \AppBundle\Entity\HomePage $child
     *
     * @return HomePage
     */
    public function addChild(\AppBundle\Entity\HomePage $child)
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * Remove child
     *
     * @param \AppBundle\Entity\HomePage $child
     */
    public function removeChild(\AppBundle\Entity\HomePage $child)
    {
        $this->children->removeElement($child);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildren()
    {
        return $this->children;
    }
}
