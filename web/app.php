<?php

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

$isLocal = file_exists(__DIR__ . '/../.enableDevMode');

$loader = require __DIR__ . '/../vendor/autoload.php';

if ($isLocal) {
    ini_set('display_errors', 'on');
    Debug::enable();
    $kernel = new AppKernel('dev', true);
} else {
    $kernel = new AppKernel('prod', false);
}

$request = Request::createFromGlobals();

Request::setTrustedProxies(
    array($request->server->get('REMOTE_ADDR')),
    Request::HEADER_X_FORWARDED_AWS_ELB
);

$response = $kernel->handle($request);

// add additional headers DCOP-157
//$response->headers->set('X-Frame-Options', 'SAMEORIGIN');
//$response->headers->set('X-XSS-Protection', '1; mode=block');
//$response->headers->set('X-Content-Type-Options', 'nosniff');
//$response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

$response->send();
$kernel->terminate($request, $response);
