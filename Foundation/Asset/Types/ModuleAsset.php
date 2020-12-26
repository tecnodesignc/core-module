<?php

namespace Modules\Core\Foundation\Asset\Types;

use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Arr;

class ModuleAsset implements AssetType
{
    /**
     * @var
     */
    private $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Get the URL
     * @return string
     */
    public function url()
    {
        return Module::asset($this->getPath());
    }

    private function getPath()
    {
        return Arr::get($this->path, 'module');
    }
}
