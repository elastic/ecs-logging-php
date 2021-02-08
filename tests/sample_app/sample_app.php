<?php

declare(strict_types=1);

require __DIR__ . './../bootstrap.php';

use Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter;
use Elastic\Types\Error as EcsError;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

$log = new Logger('MyLogger');
$handler = new StreamHandler('php://stdout', Logger::DEBUG);
$handler->setFormatter(new ElasticCommonSchemaFormatter());
$log->pushHandler($handler);

$log->notice('Hi, I am the spec for the ECS logging libraries.');

function f1()
{
    throw new RuntimeException('My example exception');
}

try {
    f1();
} catch (RuntimeException $ex) {
    $log->error(
        'My example log message',
        [
            'error'    => new EcsError($ex),
            'labels'   => ['my_ctx_key' => 'my_ctx_value'],
            'trace.id' => 'abc-xyz',
        ]
    );
}
