# ECS Logging for PHP

Transform your application logs to structured logs that comply with the [Elastic Common Schema (ECS)](https://www.elastic.co/guide/en/ecs/current/ecs-reference.html).
In combination with [filebeat](https://www.elastic.co/products/beats/filebeat) you can send your logs directly to Elasticsearch and leverage [Kibana's Logs UI](https://www.elastic.co/guide/en/infrastructure/guide/current/logs-ui-overview.html) to inspect all logs in one single place.
This library allows you to provide more observability for your PHP applications as can e.g. corrolate your logs with e.g. APM traces.

## Usage

Please note that the major version of this library is compatible with the major version of ECS.

### Install

```
composer require elastic/ecs-logging-php
```

### Monolog Formater
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

## Library Support
Currently only [Monolog:2.*](https://github.com/Seldaek/monolog) is supported.

## References
* Introduction to ECS [blog post](https://www.elastic.co/blog/introducing-the-elastic-common-schema).
* Logs UI [blog post](https://www.elastic.co/blog/infrastructure-and-logs-ui-new-ways-for-ops-to-interact-with-elasticsearch).

## License
This software is licensed under the [Apache 2 license](https://github.com/elastic/ecs-logging-php/blob/master/LICENSE).
