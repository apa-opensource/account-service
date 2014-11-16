<?php
/**
 * Created by PhpStorm.
 * User: ceilers
 * Date: 29.10.14
 * Time: 22:34
 */

namespace FNC\Bundle\AccountServiceBundle\Converter;

use FNC\Bundle\AccountServiceBundle\Entity\Account;

class AccountConverter implements ConverterInterface
{
    public function canHandle($object)
    {
        return $object instanceof Account;
    }

    public function transform($object)
    {
        /* @var Account $object */

        return array(
            'number'    => $object->getNumber(),
            'pin'       => $object->getPin(),
            'balance'   => $object->getBalance(),
            'currency'  => $object->getCurrency(),
            'disabled'  => $object->isDisabled(),
            'type'      => $object->getType()
        );
    }
}
