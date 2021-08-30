<?php

namespace Modules\Core\Http\Controllers\Admin;

use Modules\Core\Pagecache\ResponseCache\ResponseCache;

class CoreController extends AdminBaseController
{


    public function __construct()
    {
        parent::__construct();
    }

    public function clearCache()
    {

        $imresponsecache = resolve(ResponseCache::class);
        //Clear cache for spatie cache.
        $imresponsecache->flush();
        //Clear page cache in html files.
        $imresponsecache->flushPageCache();

        return redirect()->route('dashboard.index')
            ->withSuccess(trans('core::core.cache cleared'));

    }
}
