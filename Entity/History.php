<?php

namespace FNC\Bundle\AccountServiceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * History
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="FNC\Bundle\AccountServiceBundle\Entity\HistoryRepository")
 */
class History
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
     * @var \stdClass
     *
     * @ORM\ManyToOne(targetEntity="Account", inversedBy="history")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id")
     */
    private $account;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timestamp", type="datetime")
     */
    private $timestamp;

    /**
     * @var string
     *
     * @ORM\Column(name="referenceCode", type="string", length=32)
     */
    private $referenceCode;

    /**
     * @var string
     *
     * @ORM\Column(name="referenceMessage", type="string", length=32, nullable=true)
     */
    private $referenceMessage;

    /**
     * @var integer
     *
     * @ORM\Column(name="amount", type="integer")
     */
    private $amount;

    /**
     * @var integer
     *
     * @ORM\Column(name="new_balance", type="integer")
     */
    private $new_balance;

    /**
     * @var string
     *
     * @ORM\Column(name="transactionCode", type="string", length=32)
     */
    private $transactionCode;

    public function __construct()
    {
        $this->timestamp = new \DateTime();
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
     * Set account
     *
     * @param  \stdClass $account
     * @return History
     */
    public function setAccount($account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return \stdClass
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set timestamp
     *
     * @param  \DateTime $timestamp
     * @return History
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * Get timestamp
     *
     * @return \DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Set referenceCode
     *
     * @param  string $referenceCode
     * @return History
     */
    public function setReferenceCode($referenceCode)
    {
        $this->referenceCode = $referenceCode;

        return $this;
    }

    /**
     * Get referenceCode
     *
     * @return string
     */
    public function getReferenceCode()
    {
        return $this->referenceCode;
    }

    /**
     * @param string $referenceMessage
     */
    public function setReferenceMessage($referenceMessage)
    {
        $this->referenceMessage = $referenceMessage;

        return $this;
    }

    /**
     * @return string
     */
    public function getReferenceMessage()
    {
        return $this->referenceMessage;
    }

    /**
     * Set amount
     *
     * @param  integer $amount
     * @return History
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
     * @param int $new_balance
     */
    public function setNewBalance($new_balance)
    {
        $this->new_balance = $new_balance;

        return $this;
    }

    /**
     * @return int
     */
    public function getNewBalance()
    {
        return $this->new_balance;
    }

    /**
     * @param string $transactionCode
     */
    public function setTransactionCode($transactionCode)
    {
        $this->transactionCode = $transactionCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getTransactionCode()
    {
        return $this->transactionCode;
    }
}
