<?php

namespace AppBundle\Twig;

class DebugExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('debug', function($e) {
                \Doctrine\Common\Util\Debug::dump($e);
            }),
        ];
    }

    public function getName()
    {
        return 'debug_extension';
    }
}
