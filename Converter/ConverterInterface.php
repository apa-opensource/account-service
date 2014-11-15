<?php
/**
 * Created by PhpStorm.
 * User: ceilers
 * Date: 29.10.14
 * Time: 22:35
 */

namespace FNC\AccountBundle\Converter;

interface ConverterInterface
{
    public function canHandle($object);

    public function transform($object);
}
