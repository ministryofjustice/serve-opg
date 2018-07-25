<?php

namespace AppBundle\Service;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

class RouteSelfDocumentor
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * RouteSelfDocumentor constructor.
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function getHtml()
    {
        $allRoutes =  $this->router->getRouteCollection()->all();

        $ret = '<h2>Available Routes</h2><ul>';
        foreach($allRoutes as $routeName => $route) { /* @var $route Route */
            $params = self::routeCompatibleParams($route);
            $ret .= '<li><a href="'.$this->router->generate($routeName, $params).'">' . $routeName . '</a></li>';
        }
        $ret .= '</ul>';

        return $ret;
    }

    /**
     * Generate random params for hte route based on the requirements
     *
     * @param Route $route
     * @return array
     */
    private static function routeCompatibleParams(Route $route)
    {
        $exprToValueMap = [
            '\d+' => '1',
            '\w+' => 'abc',
        ];
        $ret = [];

        foreach($route->getRequirements() as $k=>$v) {
            $ret[$k] = isset($exprToValueMap[$v]) ? $exprToValueMap[$v] : 'na';
        }

        return $ret;
    }

}
