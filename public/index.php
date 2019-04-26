<?php

use App\Kernel;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__) . '/config/bootstrap.php';

$isLocal = file_exists(__DIR__ . '/../.enableDevMode');

if ($isLocal) {
    ini_set('display_errors', 'on');
    Debug::enable();
    $kernel = new AppKernel('dev', true);
} else {
    $kernel = new AppKernel('prod', false);
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts([$trustedHosts]);
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);

// add additional headers DCOP-157
$response->headers->set('X-Frame-Options', 'SAMEORIGIN');
$response->headers->set('X-XSS-Protection', '1; mode=block');
$response->headers->set('X-Content-Type-Options', 'nosniff');
$response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

$response->send();
$kernel->terminate($request, $response);
