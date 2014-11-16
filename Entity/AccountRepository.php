<?php

namespace FNC\Bundle\AccountServiceBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * AccountRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AccountRepository extends EntityRepository
{
    /**
     * @param $number
     * @return Account
     */
    public function findOneByNumber($number)
    {
        return $this->findOneBy(array('number' => $number));
    }
}
