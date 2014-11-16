<?php
/**
 * Created by PhpStorm.
 * User: ceilers
 * Date: 29.10.14
 * Time: 21:39
 */

namespace FNC\Bundle\AccountServiceBundle\Service;

use Doctrine\ORM\EntityManager;
use FNC\Bundle\AccountServiceBundle\Entity\Account;
use FNC\Bundle\AccountServiceBundle\Entity\History;
use FNC\Bundle\AccountServiceBundle\Generator\Generator;

class Service
{
    /**
     * @integer
     */
    const ERR_ACCOUNT_ALREADY_EXISTS = 1414954648;

    /**
     * @integer
     */
    const ERR_INVALID_CURRENCY = 1414954628;

    /**
     * @integer
     */
    const ERR_INVALID_TYPE = 1414954627;

    /**
     * @integer
     */
    const ERR_INVALID_TRANSACTION_CODE = 1413843064;

    /**
     * @integer
     */
    const ERR_INVALID_REFERENCE_CODE = 1413843064;

    /**
     * @integer
     */
    const ERR_CURRENCY_MISSMATCH = 1413843062;

    /**
     * @integer
     */
    const ERR_ACCOUNT_DISABLED = 1413843063;

    /**
     * @integer
     */
    const ERR_TRANSACTION_CODE_USED = 1413843165;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \FNC\Bundle\AccountServiceBundle\Generator\Generator
     */
    protected $generator;

    /**
     * @var array
     */
    protected $types;

    /**
     * @var array
     */
    protected $currencies;

    /**
     * @var \Psr\Log\LoggerInterface|\Psr\Log\NullLogger
     */
    protected $logger;

    /**
     * @param EntityManager            $em
     * @param Generator                $generator
     * @param array                    $types
     * @param array                    $currencies
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        EntityManager $em,
        Generator $generator,
        array $types,
        array $currencies,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->em = $em;

        $this->generator = $generator;

        $this->types = $types;

        $this->currencies = $currencies;

        $this->logger = $logger;
    }

    /**
     * @param      $amount
     * @param      $currency
     * @param      $referenceCode
     * @param      $referenceMessage
     * @param      $transactionCode
     * @param      $type
     * @param null $pin
     * @param null $number
     * @return Account
     * @throws \Exception
     */
    public function create(
        $amount,
        $currency,
        $referenceCode,
        $referenceMessage,
        $transactionCode,
        $type,
        $pin = null,
        $number = null
    ) {
        if ($referenceCode === null) {
            throw new \Exception('Missing Reference Code', self::ERR_INVALID_REFERENCE_CODE);
        }

        if ($transactionCode === null) {
            throw new \Exception('Missing Transaction Code', self::ERR_INVALID_TRANSACTION_CODE);
        }

        if (array_search($currency, $this->currencies, true) === false) {
            throw new \Exception('Ivalid Currency given', self::ERR_INVALID_CURRENCY);
        }

        if (array_search($type, $this->types, true) === false) {
            throw new \Exception('Ivalid Type given', self::ERR_INVALID_TYPE);
        }

        $repo = $this->em->getRepository('FNCAccountServiceBundle:Account');

        if ($number === null) {
            $number = $this->generateNumber();
        } else {
            if ($repo->findOneByNumber($number) !== null) {
                /* Optional Exception - Error Communication - Database Constraint existing */
                throw new \Exception(sprintf('Number %s already existing', $number), self::ERR_ACCOUNT_ALREADY_EXISTS);
            }
        }

        $account = new Account();

        $account
            ->setBalance($amount)
            ->setCurrency($currency)
            ->setType($type)
            ->setPin($pin)
            ->setNumber($number);

        $this->em->persist($account);

        $history = $this->createHistoryEntity(
            $account,
            $amount,
            $amount,
            $referenceCode,
            $referenceMessage,
            $transactionCode
        );

        $this->em->persist($history);

        $this->em->flush();

        return $account;
    }

    /**
     * @param Account $account
     * @param         $amount
     * @param         $currency
     * @param         $referenceCode
     * @param         $referenceMessage
     * @param         $transactionCode
     * @return int|number
     * @throws \Exception
     */
    public function booking(Account $account, $amount, $currency, $referenceCode, $referenceMessage, $transactionCode)
    {
        $historyRepo = $this->em->getRepository('FNCAccountServiceBundle:History');

        $rest = 0;

        if ($account->getCurrency() != $currency) {
            throw new \Exception('Currency missmatch', self::ERR_CURRENCY_MISSMATCH);
        }

        if ($account->isDisabled()) {
            throw new \Exception('Account disabled', self::ERR_ACCOUNT_DISABLED);
        }

        if ($referenceCode === null) {
            throw new \Exception('Missing Reference Code', self::ERR_INVALID_REFERENCE_CODE);
        }

        if ($transactionCode === null) {
            throw new \Exception('Missing Transaction Code', self::ERR_INVALID_TRANSACTION_CODE);
        }

        $transactionAmount = $historyRepo->findSumByAccountAndTransactionCode($account, $transactionCode);

        if ($transactionAmount !== null) {
            if ($amount > 0 || ($transactionAmount + $amount) < 0) {
                throw new \Exception('Transaction used', self::ERR_TRANSACTION_CODE_USED);
            }
        }

        $balance = $account->getBalance();

        $balance = $balance + $amount;

        if ($balance < 0) {
            $rest = abs($balance);
            $balance = 0;
        }

        $amount = $amount - $rest;

        $account->setBalance($balance);

        $history = $this->createHistoryEntity(
            $account,
            $amount,
            $balance,
            $referenceCode,
            $referenceMessage,
            $transactionCode
        );

        $this->em->persist($account);

        $this->em->persist($history);

        $this->em->flush();

        return $rest;
    }

    /**
     * @param Account $account
     */
    public function cancel(Account $account)
    {
        $account->setDisabled(true);

        $this->em->persist($account);
        $this->em->flush($account);
    }

    /**
     * @param Account $account
     */
    public function activate(Account $account)
    {
        $account->setDisabled(false);

        $this->em->persist($account);
        $this->em->flush($account);
    }

    /**
     * @return string
     */
    private function generateNumber()
    {
        $number = $this->generator->generate();

        $repo = $this->em->getRepository('FNCAccountServiceBundle:Account');

        if ($repo->findOneByNumber($number) !== null) {
            return $this->generateNumber();
        }

        return $number;
    }

    /**
     * @param Account $account
     * @param integer $amount
     * @param integer $balance
     * @param string  $referenceCode
     * @param string  $referenceMessage
     * @param string  $transactionCode
     * @return History
     */
    private function createHistoryEntity(
        Account $account,
        $amount,
        $balance,
        $referenceCode,
        $referenceMessage,
        $transactionCode
    ) {
        $history = new History();

        $history
            ->setAccount($account)
            ->setAmount($amount)
            ->setNewBalance($balance)
            ->setReferenceCode($referenceCode)
            ->setReferenceMessage($referenceMessage)
            ->setTransactionCode($transactionCode);

        return $history;
    }
}
