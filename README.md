[![Build Status](https://apm-ci.elastic.co/buildStatus/icon?job=apm-agent-php%2Fecs-logging-php-mbp%2Fmain)](https://apm-ci.elastic.co/job/apm-agent-php/job/ecs-logging-php-mbp/job/main/)

# ECS Logging for PHP

Transform your application logs to structured logs that comply with the [Elastic Common Schema (ECS)](https://www.elastic.co/guide/en/ecs/current/ecs-reference.html).
In combination with [Filebeat](https://www.elastic.co/products/beats/filebeat), send your logs directly to Elasticsearch and leverage [Kibana's Logs app](https://www.elastic.co/guide/en/observability/current/monitor-logs.html) to inspect all of your logs in a single place.
This provides more observability for your PHP applications, for example, by correlating your logs with APM traces.

See the [PHP ECS logging documentation](https://www.elastic.co/guide/en/ecs-logging/php/current/intro.html) to get started, or the [ecs-logging repo](https://github.com/elastic/ecs-logging) for other ECS logging libraries and more resources about ECS & logging.

---

## Install
```
composer require elastic/ecs-logging
```

## Examples and Usage
* [Monolog v2.0](https://github.com/elastic/ecs-logging-php/blob/main/docs/Monolog_v2.md)

## Library Support
* Currently only [Monolog:2.*](https://github.com/Seldaek/monolog) is supported.
* The major version of this library is compatible with the major version of ECS.

## References
* [Documentation](https://www.elastic.co/guide/en/ecs-logging/php/current/intro.html)
* Introduction to ECS [blog post](https://www.elastic.co/blog/introducing-the-elastic-common-schema).
* Logs UI [blog post](https://www.elastic.co/blog/infrastructure-and-logs-ui-new-ways-for-ops-to-interact-with-elasticsearch).

## Contributing

See [contributing documentation](CONTRIBUTING.md).

## License
This software is licensed under the [Apache 2 license](https://github.com/elastic/ecs-logging-php/blob/main/LICENSE).
