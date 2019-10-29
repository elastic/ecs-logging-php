# Monolog

## Initialize the Formatter
```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter;

$logger = new Logger('my_ecs_logger');
$formatter = new ElasticCommonSchemaFormatter();
$handler = new StreamHandler('<path-to-log-dir>/application.json', Logger::INFO);
$handler->setFormatter($formatter);
$logger->pushHandler($handler);
```

## Log Throwable's
In order to enrich your error log message with [`Throwable`](https://www.php.net/manual/en/class.throwable.php)'s data, you need to pass
the _caught_ exception or error with the key `throwable` in the context of the log message.
```php
$logger->error($t->getMessage(), ['throwable' => $t]);
```

## Trace Context
You can add a trace context to every log message by leveraging the `trace` key in contex to pass a trace Id.
```php
$logger->notice('I am a log message with a trace id, so you can do awesome things in the Logs UI', ['trace' => $traceId]);
```
