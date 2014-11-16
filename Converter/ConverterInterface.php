<?php
/**
 * Created by PhpStorm.
 * User: ceilers
 * Date: 29.10.14
 * Time: 22:35
 */

namespace FNC\Bundle\AccountServiceBundle\Converter;

interface ConverterInterface
{
    /**
     * @param $object
     * @return mixed
     */
    public function canHandle($object);

    /**
     * @param $object
     * @return mixed
     */
    public function transform($object);
}
