[![Build Status](https://apm-ci.elastic.co/buildStatus/icon?job=apm-agent-php%2Fecs-logging-php-mbp%2Fmaster)](https://apm-ci.elastic.co/job/apm-agent-php/job/ecs-logging-php-mbp/job/master/)

# ECS Logging for PHP

Transform your application logs to structured logs that comply with the [Elastic Common Schema (ECS)](https://www.elastic.co/guide/en/ecs/current/ecs-reference.html).
In combination with [filebeat](https://www.elastic.co/products/beats/filebeat) you can send your logs directly to Elasticsearch and leverage [Kibana's Logs UI](https://www.elastic.co/guide/en/infrastructure/guide/current/logs-ui-overview.html) to inspect all logs in one single place.
This library allows you to provide more observability for your PHP applications as can e.g. corrolate your logs with e.g. APM traces.
See [ecs-logging](https://github.com/elastic/ecs-logging) for other ECS logging libraries and more resources about ECS & logging.

---

**Please note** that this library is in a **beta** version and backwards-incompatible changes might be introduced in future releases. While we strive to comply to [semver](https://semver.org/), we can not guarantee to avoid breaking changes in minor releases.

---

## Install
```
composer require elastic/ecs-logging
```

## Examples and Usage
* [Monolog v2.0](https://github.com/elastic/ecs-logging-php/blob/master/docs/Monolog_v2.md)

## Library Support
* Currently only [Monolog:2.*](https://github.com/Seldaek/monolog) is supported.
* The major version of this library is compatible with the major version of ECS.

## References
* Introduction to ECS [blog post](https://www.elastic.co/blog/introducing-the-elastic-common-schema).
* Logs UI [blog post](https://www.elastic.co/blog/infrastructure-and-logs-ui-new-ways-for-ops-to-interact-with-elasticsearch).

## Test
```
composer test
```

## License
This software is licensed under the [Apache 2 license](https://github.com/elastic/ecs-logging-php/blob/master/LICENSE).
