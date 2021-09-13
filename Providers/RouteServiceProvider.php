<?php

namespace Modules\Core\Providers;



class RouteServiceProvider extends RoutingServiceProvider
{
    /**
     * @return string
     */
    protected function getFrontendRoute()
    {
        // TODO: Implement getFrontendRoute() method.
    }
    /**
     * @return string
     */
    protected function getBackendRoute()
    {
        return __DIR__ . '/../Http/backendRoutes.php';
    }
    /**
     * @return string
     */
    protected function getApiRoute()
    {
        // TODO: Implement getApiRoute() method.
    }
}
