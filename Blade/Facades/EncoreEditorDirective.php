<?php

namespace Modules\Core\Blade\Facades;

use Illuminate\Support\Facades\Facade;

class EncoreEditorDirective extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'core.encore.editor';
    }
}
