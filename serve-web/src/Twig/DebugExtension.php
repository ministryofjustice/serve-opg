<?php

namespace App\Twig;

use Symfony\Component\VarDumper\VarDumper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DebugExtension extends AbstractExtension
{
    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('debug', function ($e): void {
                VarDumper::dump($e);
            }),
        ];
    }

    public function getName(): string
    {
        return 'debug_extension';
    }
}
