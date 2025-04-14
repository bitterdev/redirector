<?php

namespace Bitter\Redirector\Routing;

use Bitter\Redirector\API\V1\Middleware\FractalNegotiatorMiddleware;
use Bitter\Redirector\API\V1\Configurator;
use Concrete\Core\Routing\RouteListInterface;
use Concrete\Core\Routing\Router;

class RouteList implements RouteListInterface
{
    public function loadRoutes(Router $router)
    {
        $router
            ->buildGroup()
            ->setNamespace('Concrete\Package\Redirector\Controller\Dialog\Support')
            ->setPrefix('/ccm/system/dialogs/redirector')
            ->routes('dialogs/support.php', 'redirector');
    }
}