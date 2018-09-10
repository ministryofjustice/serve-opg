<?php

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

$isLocal = $_SERVER['HTTP_HOST'] === 'localhost:8888'
    && !file_exists(__DIR__ . '/../.enableProdMode');

$loader = require __DIR__ . '/../vendor/autoload.php';

if ($isLocal) {
    ini_set('display_errors', 'on');
    ini_set('date.timezone', 'Europe/London');
    Debug::enable();
}

if ($isLocal) {
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
