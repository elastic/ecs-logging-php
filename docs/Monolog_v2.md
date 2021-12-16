# Monolog v2.x

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

## Use ECS Types to enrich your logs

### Log Exceptions/Errors/Throwables
In order to enrich a log event with PHP's [`Throwable`](https://www.php.net/manual/en/class.throwable.php)'s, you need to add to wrap the exception as following.
```php
use Elastic\Types\Error as EcsError;

try {
    //
    // something went wrong
    //
}
catch(\Exception $exception) {
    $logger->error('some meaningful message', ['error' => new EcsError($exception)]);
    // log and do other things ..
}
```
ECS [docs](https://www.elastic.co/guide/en/ecs/current/ecs-error.html) | Service [class](https://github.com/elastic/ecs-logging-php/blob/main/src/Elastic/Types/Error.php)

### Service
The service context enables you to provide more attributes describing your service. Setting a version can help you track system behaviour over time.
```php
use Elastic\Types\Service;

$serviceContext = new Service();
$serviceContext->setName('my-service-005');
$serviceContext->setVersion('1.2.42');

$logger->notice('this message adds service context, nice :)', ['service' => $serviceContext]);
```
ECS [docs](https://www.elastic.co/guide/en/ecs/current/ecs-service.html) | Service [class](https://github.com/elastic/ecs-logging-php/blob/main/src/Elastic/Types/Service.php)

### User
The user context allows you to enrich your log entries with user specific attributes such as `user.id` or `user.name` to simplify the discovery of specific log events.
```php
use Elastic\Types\User;

$userContext = new User();
$userContext->setId(12345);
$userContext->setEmail('hello@example.com');

$logger->notice('heya, the context helps you to trace logs more effective', ['user' => $userContext]);
```
ECS [docs](https://www.elastic.co/guide/en/ecs/current/ecs-user.html) | Service [class](https://github.com/elastic/ecs-logging-php/blob/main/src/Elastic/Types/User.php)

Please be aware that a method `User::setHash` is available, if you want to obfuscate `user.id`, `user.name`, etc.

### Tracing
You can add a tracing context to every log message by leveraging the `trace` key in contex to pass a trace Id.
```php
use Elastic\Types\Tracing;

$tracingContext = new Tracing('<trace-id>', '<transaction-id>');

$logger->notice('I am a log message with a trace id, so you can do awesome things in the Logs UI', ['tracing' => $tracingContext]);
```
ECS [docs](https://www.elastic.co/guide/en/ecs/current/ecs-tracing.html) | Tracing [class](https://github.com/elastic/ecs-logging-php/blob/main/src/Elastic/Types/Tracing.php)
