<?php

/**
 * Simple AlphaGov notification mock service, accepting `sendEmail()`
 *  (`POST /v2/notifications/email`) calls,
 *  storing them into in a temporary file,
 *  and returning them from the `/mock-data` endpoint (GET/DELETE)
 *
 * , that stores the email params into the disk, and returns them back when asked
 * https://github.com/alphagov/notifications-php-client
 */
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$body = json_decode(file_get_contents('php://input'), true);
$mailLogPath = '/tmp/mail.log';

// initialize log file
if (!file_exists($mailLogPath)) {
    file_put_contents($mailLogPath, serialize([]));
}
$logData = unserialize(file_get_contents($mailLogPath));


// route request
$ret = [
    'error' => 'no commands matched',
    'debug-data' => $_SERVER
];
if ($method == 'POST' && $uri == '/v2/notifications/email') {
    $mailId = time() . rand(1, PHP_INT_MAX);
    $logData[$mailId] = $body;
    file_put_contents($mailLogPath, serialize($logData));
    $ret = [
        'id' => $mailId,
        'reference' => 'notify-mock'
    ];
} else if ($method == 'GET' && $uri == '/mock-data') {
    $ret = $logData;
} else if ($method == 'DELETE' && $uri == '/mock-data') {
    file_put_contents($mailLogPath, serialize([]));
    $ret = 'mock data deleted';
}

echo json_encode($ret);
