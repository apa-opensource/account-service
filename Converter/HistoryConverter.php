<?php
/**
 * Created by PhpStorm.
 * User: ceilers
 * Date: 29.10.14
 * Time: 22:34
 */

namespace FNC\AccountBundle\Converter;

use FNC\AccountBundle\Entity\History;

class HistoryConverter implements ConverterInterface
{
    public function canHandle($object)
    {
        return $object instanceof History;
    }

    /**
     * @param  History $object
     * @return array
     */
    public function transform($object)
    {
        return array(
            'newBalance'        => $object->getNewBalance(),
            'amount'            => $object->getAmount(),
            'referenceCode'     => $object->getReferenceCode(),
            'referenceMessage'  => $object->getReferenceMessage(),
            'transactionCode'   => $object->getTransactionCode(),
            'timestamp'         => $object->getTimestamp()
        );
    }

}
