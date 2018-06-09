<?php

namespace Labudzinski\TestFrameworkBundle\Component\Routing\Resolver;

use Labudzinski\TestFrameworkBundle\Component\PhpUtils\ArrayUtil;
use Symfony\Component\Routing\Route;

class SortableRouteCollection extends EnhancedRouteCollection
{
    /**
     * Sorts the routes by priority
     */
    public function sortByPriority()
    {
        $routes = $this->all();
        ArrayUtil::sortBy(
            $routes,
            true,
            function (Route $route) {
                return $route->getOption('priority') ?: 0;
            }
        );
        $this->setRoutes($routes);
    }
}
