<?php
/**
 * Created by PhpStorm.
 * User: ceilers
 * Date: 29.10.14
 * Time: 22:39
 */

namespace FNC\Bundle\AccountServiceBundle\Converter;

class ConverterChain
{
    /**
     * @var ConverterInterface[]
     */
    protected $converter;

    /**
     * @param array $converter
     */
    public function __construct(array $converter)
    {
        $this->converter = $converter;
    }

    /**
     * @param $object
     * @return mixed
     */
    public function convert($object)
    {
        foreach ($this->converter as $converter) {
            if ($converter->canHandle($object)) {
                return $converter->transform($object);
            }
        }
    }
}
