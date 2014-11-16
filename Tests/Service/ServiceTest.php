<?php
/**
 * Created by PhpStorm.
 * User: ceilers
 * Date: 13.11.14
 * Time: 21:04
 */

namespace FNC\Bundle\AccountServiceBundle\Tests\Service;


use FNC\Bundle\AccountServiceBundle\Entity\Account;
use FNC\Bundle\AccountServiceBundle\Service\Service;
use FNC\Bundle\AccountServiceBundle\Tests\AbstractTest;
use FNC\Bundle\AccountServiceBundle\Tests\Service\Mock\Doctrine\ORM\EntityManagerMock;
use FNC\Bundle\AccountServiceBundle\Tests\Service\Mock\GeneratorMock;
use Symfony\Component\HttpKernel\Log\NullLogger;

class ServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @string
     */
    const ACCOUNT_DEFAULT = '200000';

    /**
     * @string
     */
    const ACCOUNT_EXISTS = '100000';

    /**
     * @string
     */
    const ACCOUNT_TRANSACTION = '300000';

    /**
     * @string
     */
    const ACCOUNT_GENERATE_ID = '400000';

    /**
     * @string
     */
    const ACCOUNT_TRANSACTION_AMOUNT = '200';

    /**
     * @string
     */
    const CURRENCY_DEFAULT = 'CREDIT';

    /**
     * @string
     */
    const CURRENCY_INVALID = 'INVALID';

    /**
     * @string
     */
    const TYPE_DEFAULT = 'CREDIT';

    /**
     * @string
     */
    const TYPE_INVALID = 'INVALID';

    /**
     * @var string[]
     */
    protected $types = array(
        ServiceTest::TYPE_DEFAULT
    );

    /**
     * @var string[]
     */
    protected $currencies = array(
        ServiceTest::CURRENCY_DEFAULT
    );

    public function testCreate()
    {
        $service = new Service($this->getEntityManagerMock(), $this->getGeneratorMock(
        ), $this->types, $this->currencies, new NullLogger());

        $account = $service->create(0, ServiceTest::CURRENCY_DEFAULT, 100, null, 100, ServiceTest::TYPE_DEFAULT);

        $this->assertTrue($account instanceof Account, 'Invalid Account Value returned');

        if ($account !== null) {
            $this->assertEquals(0, $account->getBalance());
            $this->assertEquals(ServiceTest::CURRENCY_DEFAULT, $account->getCurrency());
            $this->assertEquals(ServiceTest::TYPE_DEFAULT, $account->getType());
        }
    }

    /**
     * @expectedException       \Exception
     * @expectedExceptionCode   FNC\AccountBundle\Service\Service::ERR_ACCOUNT_ALREADY_EXISTS
     */
    public function testCreateAlreadyExists()
    {
        $service = new Service($this->getEntityManagerMock(), $this->getGeneratorMock(
        ), $this->types, $this->currencies, new NullLogger());

        $service->create(
            0,
            ServiceTest::CURRENCY_DEFAULT,
            100,
            null,
            100,
            ServiceTest::TYPE_DEFAULT,
            null,
            ServiceTest::ACCOUNT_EXISTS
        );
    }

    /**
     * @expectedException       \Exception
     * @expectedExceptionCode   FNC\AccountBundle\Service\Service::ERR_INVALID_CURRENCY
     */
    public function testCreateInvalidCurrency()
    {
        $service = new Service($this->getEntityManagerMock(), $this->getGeneratorMock(
        ), $this->types, $this->currencies, new NullLogger());

        $service->create(0, ServiceTest::CURRENCY_INVALID, 100, null, 100, ServiceTest::TYPE_DEFAULT);
    }

    /**
     * @expectedException       \Exception
     * @expectedExceptionCode   FNC\AccountBundle\Service\Service::ERR_INVALID_TYPE
     */
    public function testCreateInvalidType()
    {
        $service = new Service($this->getEntityManagerMock(), $this->getGeneratorMock(
        ), $this->types, $this->currencies, new NullLogger());

        $service->create(0, ServiceTest::CURRENCY_DEFAULT, 100, null, 100, ServiceTest::TYPE_INVALID);
    }

    /**
     * @expectedException       \Exception
     * @expectedExceptionCode   FNC\AccountBundle\Service\Service::ERR_INVALID_TRANSACTION_CODE
     */
    public function testCreateMissingTransactionCode()
    {
        $service = new Service($this->getEntityManagerMock(), $this->getGeneratorMock(
        ), $this->types, $this->currencies, new NullLogger());

        $service->create(0, ServiceTest::CURRENCY_DEFAULT, 100, null, null, ServiceTest::TYPE_DEFAULT);
    }

    /**
     * @expectedException       \Exception
     * @expectedExceptionCode   FNC\AccountBundle\Service\Service::ERR_INVALID_REFERENCE_CODE
     */
    public function testCreateMissingReferenceCode()
    {
        $service = new Service($this->getEntityManagerMock(), $this->getGeneratorMock(
        ), $this->types, $this->currencies, new NullLogger());

        $service->create(0, ServiceTest::CURRENCY_DEFAULT, null, null, 100, ServiceTest::TYPE_DEFAULT);
    }


    public function testBookingLoad()
    {
        $account = $this->getAccount(
            0,
            ServiceTest::CURRENCY_DEFAULT,
            ServiceTest::TYPE_DEFAULT,
            ServiceTest::ACCOUNT_DEFAULT
        );

        $service = new Service($this->getEntityManagerMock(), $this->getGeneratorMock(
        ), $this->types, $this->currencies, new NullLogger());

        $this->assertEquals(0, $service->booking($account, 100, ServiceTest::CURRENCY_DEFAULT, 100, null, 100));

        $this->assertEquals(100, $account->getBalance());
    }

    public function testBookingRedeem()
    {
        $account = $this->getAccount(
            100,
            ServiceTest::CURRENCY_DEFAULT,
            ServiceTest::TYPE_DEFAULT,
            ServiceTest::ACCOUNT_DEFAULT
        );

        $service = new Service($this->getEntityManagerMock(), $this->getGeneratorMock(
        ), $this->types, $this->currencies, new NullLogger());

        $this->assertEquals(0, $service->booking($account, -100, ServiceTest::CURRENCY_DEFAULT, 100, null, 100));

        $this->assertEquals(0, $account->getBalance());

        $account = $this->getAccount(
            50,
            ServiceTest::CURRENCY_DEFAULT,
            ServiceTest::TYPE_DEFAULT,
            ServiceTest::ACCOUNT_DEFAULT
        );

        $this->assertEquals(50, $service->booking($account, -100, ServiceTest::CURRENCY_DEFAULT, 100, null, 100));

        $this->assertEquals(0, $account->getBalance());
    }

    /**
     * @expectedException       \Exception
     * @expectedExceptionCode   FNC\AccountBundle\Service\Service::ERR_CURRENCY_MISSMATCH
     */
    public function testBookingCurrencyMissmatch()
    {
        $account = $this->getAccount(
            0,
            ServiceTest::CURRENCY_DEFAULT,
            ServiceTest::TYPE_DEFAULT,
            ServiceTest::ACCOUNT_DEFAULT
        );

        $service = new Service($this->getEntityManagerMock(), $this->getGeneratorMock(
        ), $this->types, $this->currencies, new NullLogger());

        $service->booking($account, 100, ServiceTest::CURRENCY_INVALID, 100, null, 100);
    }

    /**
     * @expectedException       \Exception
     * @expectedExceptionCode   FNC\AccountBundle\Service\Service::ERR_ACCOUNT_DISABLED
     */
    public function testBookingAccountDisabled()
    {
        $account = $this->getAccount(
            0,
            ServiceTest::CURRENCY_DEFAULT,
            ServiceTest::TYPE_DEFAULT,
            ServiceTest::ACCOUNT_DEFAULT,
            true
        );

        $service = new Service($this->getEntityManagerMock(), $this->getGeneratorMock(
        ), $this->types, $this->currencies, new NullLogger());

        $service->booking($account, 100, ServiceTest::CURRENCY_DEFAULT, 100, null, 100);
    }

    /**
     * @expectedException       \Exception
     * @expectedExceptionCode   FNC\AccountBundle\Service\Service::ERR_INVALID_REFERENCE_CODE
     */
    public function testBookingInvalidReferenceCode()
    {
        $account = $this->getAccount(
            0,
            ServiceTest::CURRENCY_DEFAULT,
            ServiceTest::TYPE_DEFAULT,
            ServiceTest::ACCOUNT_DEFAULT
        );

        $service = new Service($this->getEntityManagerMock(), $this->getGeneratorMock(
        ), $this->types, $this->currencies, new NullLogger());

        $service->booking($account, 100, ServiceTest::CURRENCY_DEFAULT, null, null, 100);
    }

    /**
     * @expectedException       \Exception
     * @expectedExceptionCode   FNC\AccountBundle\Service\Service::ERR_INVALID_TRANSACTION_CODE
     */
    public function testBookingInvalidTransactionCode()
    {
        $account = $this->getAccount(
            0,
            ServiceTest::CURRENCY_DEFAULT,
            ServiceTest::TYPE_DEFAULT,
            ServiceTest::ACCOUNT_DEFAULT
        );

        $service = new Service($this->getEntityManagerMock(), $this->getGeneratorMock(
        ), $this->types, $this->currencies, new NullLogger());

        $service->booking($account, 100, ServiceTest::CURRENCY_DEFAULT, 100, null, null);
    }

    public function testBookingTransactionCodeFull()
    {
        $account = $this->getAccount(
            self::ACCOUNT_TRANSACTION_AMOUNT,
            ServiceTest::CURRENCY_DEFAULT,
            ServiceTest::TYPE_DEFAULT,
            self::ACCOUNT_TRANSACTION
        );

        $service = new Service($this->getEntityManagerMock(), $this->getGeneratorMock(
        ), $this->types, $this->currencies, new NullLogger());

        $this->assertEquals(0, $service->booking($account, -200, ServiceTest::CURRENCY_DEFAULT, 100, null, 100));
    }

    /**
     * @expectedException       \Exception
     * @expectedExceptionCode   FNC\AccountBundle\Service\Service::ERR_TRANSACTION_CODE_USED
     */
    public function testBookingTransactionCodeOverloaded()
    {
        $account = $this->getAccount(
            self::ACCOUNT_TRANSACTION_AMOUNT,
            ServiceTest::CURRENCY_DEFAULT,
            ServiceTest::TYPE_DEFAULT,
            self::ACCOUNT_TRANSACTION
        );

        $service = new Service($this->getEntityManagerMock(), $this->getGeneratorMock(
        ), $this->types, $this->currencies, new NullLogger());

        $this->assertEquals(0, $service->booking($account, -300, ServiceTest::CURRENCY_DEFAULT, 100, null, 100));
    }

    public function testCancel()
    {
        $account = $this->getAccount(
            0,
            ServiceTest::CURRENCY_DEFAULT,
            ServiceTest::TYPE_DEFAULT,
            ServiceTest::ACCOUNT_DEFAULT,
            true
        );

        $service = new Service($this->getEntityManagerMock(), $this->getGeneratorMock(
        ), $this->types, $this->currencies, new NullLogger());

        $service->cancel($account);

        $this->assertTrue($account->isDisabled());
    }

    public function testActivate()
    {
        $account = $this->getAccount(
            0,
            ServiceTest::CURRENCY_DEFAULT,
            ServiceTest::TYPE_DEFAULT,
            ServiceTest::ACCOUNT_DEFAULT,
            true
        );

        $service = new Service($this->getEntityManagerMock(), $this->getGeneratorMock(
        ), $this->types, $this->currencies, new NullLogger());

        $service->activate($account);

        $this->assertFalse($account->isDisabled());
    }


    public function getAccount($amount, $currency, $type, $number, $disabled = false, $pin = null)
    {
        $account = new Account();

        $account
            ->setBalance($amount)
            ->setCurrency($currency)
            ->setType($type)
            ->setNumber($number)
            ->setPin($pin)
            ->setDisabled($disabled);

        return $account;
    }

    public function getGeneratorMock()
    {
        $mock = $this->getMock('FNC\Bundle\AccountServiceBundle\Generator\Generator');

        $mock
            ->expects($this->any())
            ->method('generate')
            ->will($this->returnValue(ServiceTest::ACCOUNT_DEFAULT));

        return $mock;
    }

    public function getEntityManagerMock()
    {
        $self = $this;

        $mock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->any())
            ->method('getRepository')
            ->will(
                $this->returnCallback(
                    function ($name) use ($self) {
                        if ($name == 'FNCAccountServiceBundle:Account') {
                            return $self->getAccountRepositoryMock();
                        }

                        if ($name == 'FNCAccountServiceBundle:History') {
                            return $self->getHistoryRepositoryMock();
                        }
                    }
                )
            );

        return $mock;
    }

    public function getHistoryRepositoryMock()
    {
        $mock = $this->getMockBuilder('FNC\Bundle\AccountServiceBundle\Entity\HistoryRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->any())
            ->method('findSumByAccountAndTransactionCode')
            ->will(
                $this->returnCallback(
                    function (Account $account, $transactionCode) {
                        if ($account->getNumber() == ServiceTest::ACCOUNT_TRANSACTION) {
                            return ServiceTest::ACCOUNT_TRANSACTION_AMOUNT;
                        }
                    }
                )
            );

        return $mock;
    }

    public function getAccountRepositoryMock()
    {
        $mock = $this->getMockBuilder('FNC\Bundle\AccountServiceBundle\Entity\AccountRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->any())
            ->method('findOneByNumber')
            ->will(
                $this->returnCallback(
                    function ($number) {
                        if ($number === ServiceTest::ACCOUNT_EXISTS) {
                            return new Account();
                        }

                        if ($number === ServiceTest::ACCOUNT_GENERATE_ID) {
                            return new Account();
                        }
                    }
                )
            );

        return $mock;
    }
} 