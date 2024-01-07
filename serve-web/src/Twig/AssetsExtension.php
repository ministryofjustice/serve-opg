<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Twig filters for assets
 *
 * e.g.
 * {{ 'images/file.png' | assetUrl }}
 * Will generate /assets/images/file.png?v=<version>
 * where <version> is the value of the DC_ASSETS_VERSION env variable (or - if not defined - the current timestamp)
 */
class AssetsExtension extends AbstractExtension
{
    private string $basePath;

    private ?string $assetsVersion = null;

    /**
     * AssetsExtension constructor.
     */
    public function __construct(string $basePath, ?string $assetsVersion)
    {
        $this->basePath = rtrim($basePath, '/');
        $this->assetsVersion = $assetsVersion;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('assetUrl', function ($originalUrl): string {
                $assetVersion = $this->assetsVersion ?: time();
                $pathToFile = ltrim($originalUrl, '/');

                return "{$this->basePath}/{$pathToFile}?v={$assetVersion}";
            }),
        ];
    }

    public function getName(): string
    {
        return 'assets_extension';
    }
}
