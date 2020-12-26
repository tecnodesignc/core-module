<?php

namespace Modules\Core\Composers;

use Illuminate\Contracts\View\View;
use Modules\Core\Foundation\EncoreCms;

class ApplicationVersionViewComposer
{
    public function compose(View $view)
    {
        if (app('encore.onBackend') === false) {
            return;
        }
        $view->with('version', EncoreCms::VERSION);
    }
}
