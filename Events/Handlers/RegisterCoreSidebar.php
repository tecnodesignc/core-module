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
    public function extendWith(Menu $menu)
    {
        $menu->group(trans('core::sidebar.content'), function (Group $group) {
            $group->weight(50);
            $group->authorize(
                $this->auth->hasAccess('core.sidebar.group')
            );

            $group->item(trans('core::sidebar.clear cache'), function (Item $item) {
                $item->icon('fa fa-eraser');
                $item->weight(-1);
                $item->route('admin.core.cache.clear');
            });
        });

        return $menu;
    }
}
