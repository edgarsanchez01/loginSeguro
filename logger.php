<?php
require_once 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

function getLogger($name) {
    $log = new Logger($name);
    $log->pushHandler(new StreamHandler('logs/log-' . date('Y-m-d') . '.log', Logger::DEBUG));
    return $log;
}
?>
