<?php

namespace Modules\Core\Events\Handlers;

use Maatwebsite\Sidebar\Group;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Item;
use Modules\Core\Sidebar\AbstractAdminSidebar;

class RegisterCoreSidebar extends AbstractAdminSidebar
{
    /**
     * Method used to define your sidebar menu groups and items
     * @param Menu $menu
     * @return Menu
     */
    public function extendWith(Menu $menu): Menu
    {
       return $menu;
    }
}
