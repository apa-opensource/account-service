<?php
/**
 * Created by PhpStorm.
 * User: ceilers
 * Date: 01.11.14
 * Time: 21:08
 */

namespace FNC\AccountBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class Builder extends ContainerAware
{
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $tr = $this->container->get('translator');

        $menu = $factory->createItem('root');

        $account = $factory->createItem($tr->trans('menu.account'));

        $account->addChild($tr->trans('menu.account.overview'), array('route' => 'management_account'));

        $account->addChild($tr->trans('menu.account.new'), array('route' => 'management_account_new'));

        $menu->addChild($account);

        return $menu;
    }
}
