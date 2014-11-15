<?php
/**
 * Created by PhpStorm.
 * User: ceilers
 * Date: 29.10.14
 * Time: 22:39
 */

namespace FNC\AccountBundle\Converter;

class ConverterChain
{
    /**
     * @var ConverterInterface[]
     */
    protected $converter;

    public function __construct(array $converter)
    {
        $this->converter = $converter;
    }

    public function convert($object)
    {
        foreach ($this->converter as $converter) {
            if ($converter->canHandle($object)) {
                return $converter->transform($object);
            }
        }
    }
}
