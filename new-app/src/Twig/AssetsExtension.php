<?php

namespace App\Twig;

/**
 * Twig filters for assets
 *
 * e.g.
 * {{ 'images/file.png' | assetUrl }}
 * Will generate /assets/images/file.png?v=<version>
 * where <version> is the value of the DC_ASSETS_VERSION env variable (or - if not defined - the current timestamp)
 */
class AssetsExtension extends \Twig_Extension
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * @var string|null
     */
    private $assetsVersion;

    /**
     * AssetsExtension constructor.
     * @param string $basePath
     * @param null|string $assetsVersion
     */
    public function __construct(string $basePath, ?string $assetsVersion)
    {
        $this->basePath = rtrim($basePath, '/');
        $this->assetsVersion = $assetsVersion;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('assetUrl', function ($originalUrl) {
                $assetVersion = getenv('DC_ASSETS_VERSION') ?: time();
                $pathToFile = ltrim($originalUrl, '/');

                return "{$this->basePath}/{$pathToFile}?v={$assetVersion}";
            }),
        ];
    }

    public function getName()
    {
        return 'assets_extension';
    }
}
