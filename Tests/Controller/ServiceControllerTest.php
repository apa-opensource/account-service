<?php

namespace FNC\AccountBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Kernel;

class ServiceControllerTest extends WebTestCase
{
    /**
     * @string
     */
    const ACCOUNT_NUMBER = '1234-5678-9123-4577';

    /**
     * @string
     */
    const ACCOUNT_PIN = '123';

    /**
     * @string
     */
    const ACCOUNT_CURRENCY = 'CREDIT';

    /**
     * @string
     */
    const ACCOUNT_TYPE_TEST = 'CREDIT';

    /**
     * @integer
     */
    const ACCOUNT_BALANCE = 100;

    protected $em;

    public function setUp()
    {
        static::$kernel = static::createKernel();
        static::$kernel->boot();

        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $repository = $this->em->getRepository('FNCAccountBundle:Account');

        /* @var Account $account*/
        foreach ($repository->findAll() as $account) {
            if ($account->getType() != self::ACCOUNT_TYPE_TEST) {
                continue;
            }
            foreach ($account->getHistory() as $history) {
                $this->em->remove($history);
            }

            $this->em->remove($account);
        }

        $this->em->flush();
    }

    public function testCreate()
    {
        $client = static::createClient();

        $crawler = $client->request('POST', '/service/create/', array(
            'amount'            => self::ACCOUNT_BALANCE,
            'currency'          => self::ACCOUNT_CURRENCY,
            'type'              => self::ACCOUNT_TYPE_TEST,
            'referenceCode'     => 1000000,
            'transactionCode'   => microtime()
        ));

        $content = $client->getResponse()->getContent();

        $this->assertTrue(
            json_decode($content) !== null,
            sprintf('Unexcepted Return Value: %2', $content));

        $this->assertTrue(
            $client->getResponse()->getStatusCode() === 200,
            sprintf('Returned Status Code: %s - %s', $client->getResponse()->getStatusCode(), $content));
    }

    public function testCreateWithNumber()
    {
        $client = static::createClient();

        $crawler = $client->request('POST', '/service/create/', array(
            'amount'            => self::ACCOUNT_BALANCE,
            'number'            => self::ACCOUNT_NUMBER,
            'currency'          => self::ACCOUNT_CURRENCY,
            'type'              => self::ACCOUNT_TYPE_TEST,
            'referenceCode'     => 1000000,
            'transactionCode'   => microtime()
        ));

        $content = $client->getResponse()->getContent();

        $object = json_decode($content);

        $this->assertTrue(
            $object !== null,
            sprintf('Unexcepted Return Value: %2', $content));

        $this->assertTrue(
            $client->getResponse()->getStatusCode() === 200,
            sprintf('Returned Status Code: %s - %s', $client->getResponse()->getStatusCode(), $content));
    }

    public function testRedeem()
    {
        $this->testCreateWithNumber();

        $url = sprintf('/service/redeem/%s/',self::ACCOUNT_NUMBER);

        $client = static::createClient();

        $i = microtime();

        $crawler = $client->request('POST', $url, array(
            'pin'               =>  self::ACCOUNT_PIN,
            'amount'            => '110',
            'currency'          => self::ACCOUNT_CURRENCY,
            'referenceCode'     => 1000,
            'transactionCode'   => $i
        ));

        $content = $client->getResponse()->getContent();

        $object = json_decode($content);

        $this->assertTrue(
            property_exists($object, 'rest') && $object->rest == 10, 'Incorrect rest');

        $this->assertTrue(
            $object !== null,
            sprintf('Unexcepted Return Value: %2', $content));

        $this->assertTrue(
            $client->getResponse()->getStatusCode() === 200,
            sprintf('Returned Status Code: %s - %s', $client->getResponse()->getStatusCode(), $content));
    }

    public function testLoad()
    {
        $this->testCreateWithNumber();

        $url = sprintf('/service/load/%s/',self::ACCOUNT_NUMBER);

        $client = static::createClient();

        $crawler = $client->request('POST', $url, array(
            'pin'               =>  self::ACCOUNT_PIN,
            'amount'            => '100',
            'currency'          => self::ACCOUNT_CURRENCY,
            'referenceCode'     => 1000,
            'transactionCode'   => microtime()
        ));

        $content = $client->getResponse()->getContent();

        $object = json_decode($content);

        $this->assertTrue(
            property_exists($object, 'balance') && $object->balance == 200, 'Incorrect Balance');

        $this->assertTrue(
            $object !== null,
            sprintf('Unexcepted Return Value: %2', $content));

        $this->assertTrue(
            $client->getResponse()->getStatusCode() === 200,
            sprintf('Returned Status Code: %s - %s', $client->getResponse()->getStatusCode(), $content));
    }

    public function testBalance()
    {
        $this->testCreateWithNumber();

        $url = sprintf('/service/balance/%s/',self::ACCOUNT_NUMBER);

        $client = static::createClient();

        $crawler = $client->request('POST', $url, array(
            'pin'   => self::ACCOUNT_PIN
        ));

        $content = $client->getResponse()->getContent();

        $this->assertTrue(
            json_decode($content) !== null,
            sprintf('Unexcepted Return Value: %2', $content));

        $this->assertTrue(
            $client->getResponse()->getStatusCode() === 200,
            sprintf('Returned Status Code: %s - %s', $client->getResponse()->getStatusCode(), $content));
    }

    public function testStatus()
    {
        $this->testCreateWithNumber();

        $url = sprintf('/service/status/%s/',self::ACCOUNT_NUMBER);

        $client = static::createClient();

        $crawler = $client->request('POST', $url, array(
            'pin'   => self::ACCOUNT_PIN
        ));

        $content = $client->getResponse()->getContent();

        $this->assertTrue(
            json_decode($content) !== null,
            sprintf('Unexcepted Return Value: %2', $content));

        $this->assertTrue(
            $client->getResponse()->getStatusCode() === 200,
            sprintf('Returned Status Code: %s - %s', $client->getResponse()->getStatusCode(), $content));
    }

    public function testInfo()
    {
        $this->testCreateWithNumber();

        $url = sprintf('/service/info/%s/',self::ACCOUNT_NUMBER);

        $client = static::createClient();

        $crawler = $client->request('POST', $url, array(
            'pin'   => self::ACCOUNT_PIN
        ));

        $content = $client->getResponse()->getContent();

        $this->assertTrue(
            json_decode($content) !== null,
            sprintf('Unexcepted Return Value: %2', $content));

        $this->assertTrue(
            $client->getResponse()->getStatusCode() === 200,
            sprintf('Returned Status Code: %s - %s', $client->getResponse()->getStatusCode(), $content));
    }

    public function testHistory()
    {
        $this->testCreateWithNumber();

        $url = sprintf('/service/history/%s/',self::ACCOUNT_NUMBER);

        $client = static::createClient();

        $crawler = $client->request('POST', $url, array(
            'pin'   => self::ACCOUNT_PIN
        ));

        $content = $client->getResponse()->getContent();

        $this->assertTrue(
            json_decode($content) !== null,
            sprintf('Unexcepted Return Value: %2', $content));

        $this->assertTrue(
            $client->getResponse()->getStatusCode() === 200,
            sprintf('Returned Status Code: %s - %s', $client->getResponse()->getStatusCode(), $content));
    }

    public function testCancel()
    {
        $this->testCreateWithNumber();

        $url = sprintf('/service/cancel/%s/',self::ACCOUNT_NUMBER);

        $client = static::createClient();

        $crawler = $client->request('POST', $url, array(
            'pin'   => self::ACCOUNT_PIN
        ));

        $content = $client->getResponse()->getContent();

        $this->assertTrue(
            json_decode($content) !== null,
            sprintf('Unexcepted Return Value: %2', $content));

        $this->assertTrue(
            $client->getResponse()->getStatusCode() === 200,
            sprintf('Returned Status Code: %s - %s', $client->getResponse()->getStatusCode(), $content));
    }

    public function testActivate()
    {
        $this->testCreateWithNumber();

        $url = sprintf('/service/activate/%s/',self::ACCOUNT_NUMBER);

        $client = static::createClient();

        $crawler = $client->request('POST', $url, array(
            'pin'   => self::ACCOUNT_PIN
        ));

        $content = $client->getResponse()->getContent();

        $this->assertTrue(
            json_decode($content) !== null,
            sprintf('Unexcepted Return Value: %2', $content));

        $this->assertTrue(
            $client->getResponse()->getStatusCode() === 200,
            sprintf('Returned Status Code: %s - %s', $client->getResponse()->getStatusCode(), $content));
    }
}
