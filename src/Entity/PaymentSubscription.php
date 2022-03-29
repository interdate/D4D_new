<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaymentSubscription
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class PaymentSubscription extends ContentPayment
{
    /**
     * @var integer
     *
     * @ORM\Column(name="amount", type="integer", nullable=true)
     */
    protected $amount;

    /**
     * @var integer
     *
     * @ORM\Column(name="price", type="integer", nullable=true)
     */
    protected $price;

    /**
     * @var string
     *
     * @ORM\Column(name="text_price", type="string", length=100, nullable=true)
     */
    protected $textPrice;

    /**
     * Set amount
     *
     * @param integer $amount
     *
     * @return PaymentSubscription
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set price
     *
     * @param integer $price
     *
     * @return PaymentSubscription
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set textPrice
     *
     * @param string $textPrice
     *
     * @return PaymentSubscription
     */
    public function setTextPrice($textPrice)
    {
        $this->textPrice = $textPrice;

        return $this;
    }

    /**
     * Get textPrice
     *
     * @return integer
     */
    public function getTextPrice()
    {
        return $this->textPrice;
    }

    public function getPeriod()
    {
//        if ($this->getPrice() == 50) {
//            $period = '12';
//        } elseif ($this->getPrice() == 55 ) {
//            $period = '6';
//        } elseif ($this->getPrice() == 60 ) {
//            $period = '3';
//        } elseif ($this->getPrice() == 99 ) {
//            $period = '1';
//        } else {
//            $period = '-1';//default
//        }
        $period = '-1';
        if ($this->getId() == 2) {
            $period = '-1';
        } elseif ($this->getId() == 3 ) {
            $period = '1';
        } elseif ($this->getId() == 4 ) {
            $period = '3';
        } elseif ($this->getId() == 5 ) {
            $period = '6';
        } elseif ($this->getId() == 6 ) {
            $period = '12';
        }
        return $period;
    }
}
