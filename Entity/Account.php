<?php

namespace FNC\Bundle\AccountServiceBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Account
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="FNC\Bundle\AccountServiceBundle\Entity\AccountRepository")
 */
class Account
{
    /**
     * @var int
     */
    const REFERENCE_CODE_DEFAULT = 0x01;

    /**
     * @var int
     */
    const REFERENCE_CODE_BACKEND = 0x02;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="string", length=32)
     */
    private $type;

    /**
     * @var boolean
     *
     * @ORM\Column(name="disabled", type="boolean", nullable=true)
     */
    private $disabled = false;

    /**
     * @var string
     *
     * @ORM\Column(name="number", type="string", length=32, unique=true)
     */
    private $number;

    /**
     * @var string
     *
     * @ORM\Column(name="pin", type="string", length=32, nullable=true)
     */
    private $pin;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=6)
     */
    private $currency;

    /**
     * @var integer
     *
     * @ORM\Column(name="balance", type="integer")
     */
    private $balance;

    /**
     * @var ArrayCollection<History>
     *
     * @ORM\OneToMany(targetEntity="History", mappedBy="account")
     */
    private $history;

    public function __construct()
    {
        $this->history = new ArrayCollection();
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
     * Set type
     *
     * @param  integer $type
     * @return Account
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set disabled
     *
     * @param  boolean $disabled
     * @return Account
     */
    public function setDisabled($disabled)
    {
        $this->disabled = $disabled;

        return $this;
    }

    /**
     * Get disabled
     *
     * @return boolean
     */
    public function isDisabled()
    {
        return $this->disabled;
    }

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set pin
     *
     * @param  string $pin
     * @return Account
     */
    public function setPin($pin)
    {
        $this->pin = $pin;

        return $this;
    }

    /**
     * Get pin
     *
     * @return string
     */
    public function getPin()
    {
        return $this->pin;
    }

    /**
     * Set currency
     *
     * @param  string $currency
     * @return Account
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set amount
     *
     * @param  integer $balance
     * @return Account
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * Get amount
     *
     * @return integer
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * Set history
     *
     * @param  array $history
     * @return Account
     */
    public function setHistory($history)
    {
        $this->history = $history;

        return $this;
    }

    /**
     * Get history
     *
     * @return array
     */
    public function getHistory()
    {
        return $this->history;
    }
}
