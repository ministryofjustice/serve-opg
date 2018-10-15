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
$response->send();
$kernel->terminate($request, $response);
