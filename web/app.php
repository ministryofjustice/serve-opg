<?php

use Symfony\Component\Debug\Debug;
// use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;

$isLocal = $_SERVER['HTTP_HOST'] === 'localhost:8888'
    && !file_exists(__DIR__ . '/../.enableProdMode');

$loader = require __DIR__ . '/../vendor/autoload.php';
//$loader = require_once __DIR__ . '/../app/bootstrap.php.cache';

// Use APC for autoloading to improve performance.
// Change 'sf2' to a unique prefix in order to prevent cache key conflicts
// with other applications also using APC.
/*
$loader = new ApcClassLoader('sf2', $loader);
$loader->register(true);
*/

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
//$kernel->loadClassCache();
//$kernel = new AppCache($kernel);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
