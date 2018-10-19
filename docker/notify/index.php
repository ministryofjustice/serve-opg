<?php

/**
 * Simple AlphaGov notification mock service, that stores the email params into the disk, and returns them back when asked
 */
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$body = json_decode(file_get_contents('php://input'), true);
$mailLogPath = '/tmp/mail.log';

if (!file_exists($mailLogPath)) {
    file_put_contents($mailLogPath, serialize([]));
}
$logData = unserialize(file_get_contents($mailLogPath));

if ($method == 'POST' && $uri == '/v2/notifications/email') {
    $mailId = time() . rand(1, PHP_INT_MAX);
    $logData[$mailId] = $body;
    file_put_contents($mailLogPath, serialize($logData));
    echo json_encode([
        'id' => $mailId,
        'reference' => 'notify-mock'
    ]);
    die;
} else if ($method == 'GET' && $uri == '/mock-data') {
    echo json_encode($logData);
    die;
} else if ($method == 'DELETE' && $uri == '/mock-data') {
    file_put_contents($mailLogPath, serialize([]));
    die;
}
