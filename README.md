# ECS Logging for PHP

Transform your application logs to structured logs that comply with the [Elastic Common Schema (ECS)](https://www.elastic.co/guide/en/ecs/current/ecs-reference.html).
In combination with [filebeat](https://www.elastic.co/products/beats/filebeat) you can send your logs directly to Elasticsearch and leverage [Kibana's Logs UI](https://www.elastic.co/guide/en/infrastructure/guide/current/logs-ui-overview.html) to inspect all logs in one single place.

## Library Support
Currently only [Monolog:2.*](https://github.com/Seldaek/monolog) is supported.

## References
* Introduction to ECS [blog post](https://www.elastic.co/blog/introducing-the-elastic-common-schema).
* Logs UI [blog post](https://www.elastic.co/blog/infrastructure-and-logs-ui-new-ways-for-ops-to-interact-with-elasticsearch).
