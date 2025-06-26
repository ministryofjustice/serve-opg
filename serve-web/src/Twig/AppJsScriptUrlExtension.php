<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppJsScriptUrlExtension extends AbstractExtension
{
    private string $appJsPath = '';

    public function __construct(private readonly string $metafilePath)
    {
        $fullMetafilePath = dirname(dirname(__DIR__)).'/public/'.$this->metafilePath;

        if (file_exists($fullMetafilePath)) {
            $json = json_decode(file_get_contents($fullMetafilePath), associative: true);

            foreach ($json['outputs'] as $name => $_) {
                if (preg_match('/app-.+\.js/', $name)) {
                    $this->appJsPath = '/build/'.basename($name);
                }
            }
        }
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('appJsScriptUrl', function (): string {
                return $this->appJsPath;
            }),
        ];
    }

    public function getName(): string
    {
        return 'app_js_script_url_extension';
    }
}
