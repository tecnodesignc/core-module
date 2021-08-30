<?php

namespace Modules\Core\Console;

use Illuminate\Console\Command;
use Modules\Core\Pagecache\ResponseCache\ResponseCache;
use Modules\Media\Jobs\RebuildThumbnails;
use Modules\Media\Repositories\FileRepository;

use Illuminate\Cache\CacheManager;

use Modules\Ihelpers\Other\ImResponseCache\ImResponseCache;


class ClearPageCache extends Command
{

    protected $signature = 'page:cache-clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the Full Page Cache.';


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $responsecache = $this->laravel->make(ResponseCache::class);
        //Clear cache for spatie cache.
        $responsecache->flush();
        //Clear page cache in html files.
        $responsecache->flushPageCache();

        $this->info('Clear the Page Cache');
        //Old way
        //$storeName = config('laravel-responsecache.cacheStore');
        //app(CacheManager::class)->store($storeName)->flush();
    }

}
