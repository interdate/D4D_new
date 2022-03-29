<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TableTextPayment
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class TableTextPayment extends ContentPayment
{
    /**
     * @var boolean
     *
     * @ORM\Column(name="pay", type="boolean")
     */
    private $pay = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="not_pay", type="boolean")
     */
    private $notPay = false;


    /**
     * Set pay
     *
     * @param boolean $pay
     *
     * @return TableTextPayment
     */
    public function setPay($pay)
    {
        $this->pay = $pay;

        return $this;
    }

    /**
     * Get pay
     *
     * @return boolean
     */
    public function getPay()
    {
        return $this->pay;
    }

    /**
     * Set notPay
     *
     * @param boolean $notPay
     *
     * @return TableTextPayment
     */
    public function setNotPay($notPay)
    {
        $this->notPay = $notPay;

        return $this;
    }

    /**
     * Get notPay
     *
     * @return boolean
     */
    public function getNotPay()
    {
        return $this->notPay;
    }
}
