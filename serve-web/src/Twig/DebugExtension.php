<?php

namespace App\Twig;

use Doctrine\Common\Util\Debug;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * debug filter, using \Doctrine\Common\Util\Debug::dump();
 *
 * {{ var | debug }}
 */
class DebugExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('debug', function ($e) {

                Debug::dump($e);
            }),
        ];
    }

    public function getName()
    {
        return 'debug_extension';
    }
}
