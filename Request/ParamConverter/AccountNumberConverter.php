<?php
/**
 * Created by PhpStorm.
 * User: ceilers
 * Date: 20.10.14
 * Time: 00:05
 */

namespace FNC\AccountBundle\Request\ParamConverter;

use Doctrine\ORM\EntityManager;
use FNC\AccountBundle\Entity\Account;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class AccountNumberConverter implements ParamConverterInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param Request $request
     * @param ParamConverter $configuration
     * @return bool
     * @throws \Exception
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        if (stripos($request->getPathInfo(), '/service') !== 0) {
            return false;
        }

        $name = $configuration->getName();

        $number  = $request->get('number');
        $pin     = $request->get('pin');

        $repository = $this->em->getRepository('FNCAccountBundle:Account');

        /* @var Account $account */
        $account = $repository->findOneByNumber($number);

        if ($account !== null && ($account->getPin() === null || $account->getPin() == $pin)) {
            $request->attributes->set($name, $account);
        }

        if($account === null) {
            throw new \Exception('Invalid Account given');
        }

        return true;
    }

    /**
     * @param ParamConverter $configuration
     * @return bool
     */
    public function supports(ParamConverter $configuration)
    {
        return $configuration->getClass() && is_a($configuration->getClass(), 'FNC\AccountBundle\Entity\Account', true);
    }
}
