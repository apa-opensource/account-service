<?php
/**
 * Created by PhpStorm.
 * User: ceilers
 * Date: 19.10.14
 * Time: 23:01
 */

namespace FNC\Bundle\AccountServiceBundle\Generator;

class Generator
{
    /**
     * @param  int    $size
     * @param  int    $count
     * @param  string $separator
     * @return string
     */
    public function generate($size = 4, $count = 4, $separator = '-')
    {
        $num = array();

        for ($i = 0; $i < $size * $count; $i++) {
            $num[] = rand(1, 9);

            if ($separator !== null && ($i + 1) < ($size * $count) && (($i + 1) % $size) === 0) {
                $num[] = $separator;
            }
        }

        return implode('', $num);
    }
}
