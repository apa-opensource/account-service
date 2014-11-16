<?php
/**
 * Created by PhpStorm.
 * User: ceilers
 * Date: 16.11.14
 * Time: 01:32
 */

namespace FNC\Bundle\AccountServiceBundle\Tests\Controller;


use FNC\Bundle\AccountServiceBundle\Controller\ServiceController;
use FNC\Bundle\AccountServiceBundle\Entity\Account;
use Symfony\Component\HttpFoundation\JsonResponse;

class ServiceControllerTest extends \PHPUnit_Framework_TestCase
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
     * @var ServiceController
     */
    public $controller;

    public function setUp()
    {
        $this->controller = new ServiceController();
        $this->controller->setContainer($this->getContainerMock());
    }

    public function testCreate()
    {
        $request = $this->getRequestMock(
            array(
                'amount' => -100,
                'currency' => ServiceControllerTest::CURRENCY_DEFAULT,
                'type' => ServiceControllerTest::TYPE_DEFAULT,
                'number' => ServiceControllerTest::ACCOUNT_DEFAULT
            )
        );

        $return = $this->controller->createAction($request);

        $this->assertEquals(ServiceControllerTest::ACCOUNT_DEFAULT, $return['number']);
    }

    public function testLoad()
    {
        $account = new Account();
        $account->setBalance(100);

        $request = $this->getRequestMock(
            array(
                'amount' => 100,
                'currency' => ServiceControllerTest::CURRENCY_DEFAULT,
                'type' => ServiceControllerTest::TYPE_DEFAULT,
                'number' => ServiceControllerTest::ACCOUNT_DEFAULT
            )
        );

        $return = $this->controller->loadAction($request, $account);

        $this->assertEquals(100, $return['balance']);
    }

    /**
     * @expectedException       \Exception
     * @expectedExceptionCode   FNC\Bundle\AccountServiceBundle\Controller\ServiceController::ERR_SERVICE_NEGATIVE_AMOUNT
     */
    public function testLoadNegativeAmount()
    {
        $account = new Account();
        $account->setBalance(100);

        $request = $this->getRequestMock(
            array(
                'amount' => -100,
                'currency' => ServiceControllerTest::CURRENCY_DEFAULT,
                'type' => ServiceControllerTest::TYPE_DEFAULT,
                'number' => ServiceControllerTest::ACCOUNT_DEFAULT
            )
        );

        $return = $this->controller->loadAction($request, $account);

        $this->assertEquals(100, $return['balance']);
    }

    public function testRedeem()
    {
        $request = $this->getRequestMock(
            array(
                'amount' => 100,
                'currency' => ServiceControllerTest::CURRENCY_DEFAULT,
                'type' => ServiceControllerTest::TYPE_DEFAULT,
                'number' => ServiceControllerTest::ACCOUNT_DEFAULT
            )
        );

        $return = $this->controller->redeemAction($request, new Account());

        $this->assertEquals(0, $return['rest']);
    }

    /**
     * @expectedException       \Exception
     * @expectedExceptionCode   FNC\Bundle\AccountServiceBundle\Controller\ServiceController::ERR_SERVICE_NEGATIVE_AMOUNT
     */
    public function testRedeemNegativeAmount()
    {
        $request = $this->getRequestMock(
            array(
                'amount' => -100,
                'currency' => ServiceControllerTest::CURRENCY_DEFAULT,
                'type' => ServiceControllerTest::TYPE_DEFAULT,
                'number' => ServiceControllerTest::ACCOUNT_DEFAULT
            )
        );

        $return = $this->controller->redeemAction($request, new Account());

        $this->assertEquals(0, $return['rest']);
    }

    public function testCancel()
    {
        $return = $this->controller->cancelAction(new Account());

        $this->assertEquals(true, $return['disabled']);
    }

    public function testActivate()
    {
        $return = $this->controller->activateAction(new Account());

        $this->assertEquals(false, $return['disabled']);
    }

    public function testStatus()
    {
        $return = $this->controller->statusAction(new Account());

        $this->assertTrue(isset($return['disabled']));
    }

    public function testBalance()
    {
        $account = new Account();

        $account
            ->setBalance(200)
            ->setCurrency(self::CURRENCY_DEFAULT);

        $return = $this->controller->balanceAction($account);

        $this->assertEquals(200, $return['balance']);
        $this->assertEquals(self::CURRENCY_DEFAULT, $return['currency']);
    }

    public function getRequestMock(array $parameters)
    {
        $request = $this->getMock('Symfony\\Component\\HttpFoundation\\Request');
        $headers = $this->getMock('Symfony\\Component\\HttpFoundation\\ParameterBag');
        $request->request->headers = $headers;

        $request
            ->expects($this->any())
            ->method('get')
            ->willReturnCallback(
                function ($key) use ($parameters) {
                    if (isset($parameters[$key])) {
                        return $parameters[$key];
                    }

                    return null;
                }
            );

        return $request;
    }

    public function getContainerMock()
    {
        $self = $this;

        $mock = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->any())
            ->method('get')
            ->willReturnCallback(
                function ($name) use ($self) {
                    if ($name === 'fnc_account.service') {
                        return $self->getServiceMock();
                    }

                    if ($name === 'fnc_account.converter_chain') {

                    }
                }
            );

        return $mock;
    }

    public function getServiceMock()
    {
        $mock = $this->getMockBuilder('FNC\Bundle\AccountServiceBundle\Service\Service')
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->any())
            ->method('create')
            ->willReturnCallback(
                function (
                    $amount,
                    $currency,
                    $referenceCode,
                    $referenceMessage,
                    $transactionCode,
                    $type,
                    $pin,
                    $number
                ) {
                    $account = new Account();

                    $account->setBalance($amount);
                    $account->setCurrency($currency);
                    $account->setType($type);
                    $account->setPin($pin);
                    $account->setNumber($number);

                    return $account;
                }
            );

        $mock
            ->method('booking')
            ->willReturnCallback(
                function ($account, $amount, $currency, $referenceCode, $referenceMessage, $transactionCode) {
                    return 0;
                }
            );

        $mock
            ->method('cancel')
            ->willReturnCallback(
                function (Account $account) {
                    $account->setDisabled(true);
                }
            );


        return $mock;
    }
} 