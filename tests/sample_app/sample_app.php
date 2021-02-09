<?php

declare(strict_types=1);

require __DIR__ . './../bootstrap.php';

use Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter;
use Elastic\Types\Error as EcsError;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;

echo 'Current timezone: ' . date_default_timezone_get() . PHP_EOL;
if (date_default_timezone_set('America/New_York') !== true) {
    die('Failed to set timezone');
}
echo 'Current timezone: ' . date_default_timezone_get() . PHP_EOL;

function f1()
{
    throw new RuntimeException('My example exception');
}

function main()
{
    $logger = new Logger('MyLogger');
    $handler = new StreamHandler('php://stdout', Logger::DEBUG);
    $handler->setFormatter(new ElasticCommonSchemaFormatter());
    $logger->pushHandler($handler);
    $logger->pushProcessor(new IntrospectionProcessor());

    $logger->notice('Hi, I am the spec for the ECS logging libraries.');

    try {
        f1();
    } catch (RuntimeException $ex) {
        $logger->error(
            'My example log message',
            [
                'error'    => new EcsError($ex),
                'labels'   => ['my_ctx_key' => 'my_ctx_value'],
                'trace.id' => 'abc-xyz',
            ]
        );
    }
}

main();
