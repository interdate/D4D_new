<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Settings
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Banner // id, position , href, img
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
     * @ORM\Column(name="href", type="string", length=255)
     */
    private $href;
    
    /**
     * @var string
     *
     * @ORM\Column(name="img", type="string", length=255)
     */
    private $img;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="click_count", type="integer", length=255)
     */
    private $clickCount;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_active", type="boolean", length=1)
     */
    private $isActive;


    /**
     * @var boolean
     *
     * @ORM\Column(name="before_login", type="boolean", length=1)
     */
    private $beforeLogin;

    /**
     * @var boolean
     *
     * @ORM\Column(name="after_login", type="boolean", length=1)
     */
    private $afterLogin;

    /**
     * @var boolean
     *
     * @ORM\Column(name="subscription_page", type="boolean", length=1)
     */
    private $subscriptionPage;

    /**
     * @var boolean
     *
     * @ORM\Column(name="profile_bottom", type="boolean", length=1)
     */
    private $profileBottom;

    /**
     * @var boolean
     *
     * @ORM\Column(name="profile_top", type="boolean", length=1)
     */
    private $profileTop;

    /**
     * @var boolean
     *
     * @ORM\Column(name="mobile_app", type="boolean", length=1)
     */
    private $mobileApp;


    public $ext;

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
     * Set contactEmail
     *
     * @param string $href
     * @return Banner
     */
    public function setHref($href)
    {
        $this->href = $href;

        return $this;
    }
    
    /**
     * Get href
     *
     * @return string
     */
    public function getHref()
    {
    	return $this->href;
    }
    
    /**
     * Set img
     *
     * @param string $img
     * @return Banner
     */
    public function setImg($img)
    {
    	$this->img = $img;
    
    	return $this;
    }

    /**
     * Get img
     *
     * @return string 
     */
    public function getImg()
    {
        if(strpos($this->img, 'https://' . $_SERVER['SERVER_NAME']) === false){
            return 'https://' . $_SERVER['SERVER_NAME'] . $this->img;
        }
        return $this->img;
    }


    /**
     * Set name
     *
     * @param string $name
     * @return Banner
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
     * Set name
     *
     * @param string $clickCount
     * @return Banner
     */
    public function setClickCount($clickCount)
    {
        $this->clickCount = $clickCount;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getClickCount()
    {
        return $this->clickCount;
    }

    /**
     * Set isActive
     *
     * @param string $isActive
     * @return Banner
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return string
     */
    public function getIsActive()
    {
        return $this->isActive;
    }


    /**
     * Set isActive
     *
     * @param string $beforeLogin
     * @return Banner
     */
    public function setBeforeLogin($beforeLogin)
    {
        $this->beforeLogin = $beforeLogin;

        return $this;
    }

    /**
     * Get beforeLogin
     *
     * @return string
     */
    public function getBeforeLogin()
    {
        return $this->beforeLogin;
    }

    /**
     * Set afterLogin
     *
     * @param string $afterLogin
     * @return Banner
     */
    public function setAfterLogin($afterLogin)
    {
        $this->afterLogin = $afterLogin;

        return $this;
    }

    /**
     * Get afterLogin
     *
     * @return string
     */
    public function getAfterLogin()
    {
        return $this->afterLogin;

     }

    /**
     * Get subscriptionPage
     *
     * @return string
     */
    public function getSubscriptionPage()
    {
        return $this->subscriptionPage;
    }

    /**
     * Set subscriptionPage
     *
     * @param string $subscriptionPage
     * @return Banner
     */
    public function setSubscriptionPage($subscriptionPage)
    {
        $this->subscriptionPage = $subscriptionPage;

        return $this;
    }

    /**
     * Get profileBottom
     *
     * @return string
     */
    public function getProfileBottom()
    {
        return $this->profileBottom;
    }

    /**
     * Set profileBottom
     *
     * @param string $profileBottom
     * @return Banner
     */
    public function setProfileBottom($profileBottom)
    {
        $this->profileBottom = $profileBottom;

        return $this;
    }

    /**
     * Get profileTop
     *
     * @return string
     */
    public function getProfileTop()
    {
        return $this->profileTop;
    }

    /**
     * Set profileTop
     *
     * @param string $profileTop
     * @return Banner
     */
    public function setProfileTop($profileTop)
    {
        $this->profileTop = $profileTop;

        return $this;
    }

    /**
     * Get mobileApp
     *
     * @return string
     */
    public function getMobileApp()
    {
        return $this->mobileApp;
    }

    /**
     * Set mobileApp
     *
     * @param string $mobileApp
     * @return Banner
     */
    public function setMobileApp($mobileApp)
    {
        $this->mobileApp = $mobileApp;

        return $this;
    }

    /**
     * Called before saving the entity
     *
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload($file)
    {
        if (null !== $file) {
            if(!is_dir($this->getUploadRootDir())){
                mkdir($this->getUploadRootDir(), 0777, true);
            }
            $this->ext = $file->guessExtension();

        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
public function upload($file)
{
    // the file property can be empty if the field is not required
    if (null === $file) {
        return;
    }

    $file->move(
        $this->getUploadRootDir(),
        $this->id . '.' .$file->guessExtension()
    );
    $this->img = '/images/banners/' .  $this->id . '.' .$this->ext;
//    $this->file = null;
}

    public function getUploadRootDir()
    {
        return $_SERVER['DOCUMENT_ROOT'] . '/images/banners/';
    }



   // public function getUploadRootDir()
}
