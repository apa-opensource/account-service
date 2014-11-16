<?php
/**
 * Created by PhpStorm.
 * User: ceilers
 * Date: 15.11.14
 * Time: 04:13
 */

namespace FNC\Bundle\AccountServiceBundle\Tests\Generator;


use FNC\Bundle\AccountServiceBundle\Generator\Generator;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerate()
    {
        $generator = new Generator();

        $this->assertRegExp('/[1-9]{3}/', $generator->generate(3));

        $this->assertRegExp('/[1-9]{4}-[1-9]{4}/', $generator->generate(4, 2));

        $this->assertRegExp('/[1-9]{2}:[1-9]{2}/', $generator->generate(2, 2, ':'));
    }
} 