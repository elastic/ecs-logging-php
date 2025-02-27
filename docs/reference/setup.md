---
mapped_pages:
  - https://www.elastic.co/guide/en/ecs-logging/php/current/setup.html
navigation_title: Get started
---

# Get started with ECS Logging PHP [setup]

::::{note}
ECS logging for PHP is currently only available for Monolog v2.*.
::::



## Step 1: Set up application logging [setup-step-1]


### Add the dependency [_add_the_dependency]

```cmd
composer require elastic/ecs-logging
```


### Configure monolog logger [_configure_monolog_logger]

`Elastic\Monolog\v2\Formatter\ElasticCommonSchemaFormatter` implements Monolog’s [`FormatterInterface`](https://github.com/Seldaek/monolog/blob/2.0.0/src/Monolog/Formatter/FormatterInterface.php) and thus it can be used when setting up Monolog logger.

For example:

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter;

$log = new Logger('MyLogger');
$handler = new StreamHandler('php://stdout', Logger::DEBUG);
$handler->setFormatter(new ElasticCommonSchemaFormatter());
$log->pushHandler($handler);

$log->warning('Be aware that...');
```

Logs the following JSON to standard output:

```json
{"@timestamp":"2021-02-07T18:08:07.229676Z","log.level":"WARNING","message":"Be aware that...","ecs.version":"1.2.0","log":{"logger":"MyLogger"}}
```

Additionally, it allows for adding additional keys to messages.

For example:

```php
$log->info('My message', ['labels' => ['my_label_key' => 'my_label_value'], 'trace.id' => 'abc-xyz']);
```

Logs the following (multi-line formatted for better readability):

```json
{
    "@timestamp": "2021-02-08T06:36:38.913824Z",
    "log.level": "INFO",
    "message": "My message",
    "ecs.version": "1.2.0",
    "log": {
        "logger": "MyLogger"
    },
    "labels": {
        "my_label_key": "my_label_value"
    },
    "trace.id": "abc-xyz"
}
```


## Step 2: Configure Filebeat [setup-step-2]

:::::::{tab-set}

::::::{tab-item} Log file
1. Follow the [Filebeat quick start](beats://docs/reference/filebeat/filebeat-installation-configuration.md)
2. Add the following configuration to your `filebeat.yaml` file.

For Filebeat 7.16+

```yaml
filebeat.inputs:
- type: filestream <1>
  paths: /path/to/logs.json
  parsers:
    - ndjson:
      overwrite_keys: true <2>
      add_error_key: true <3>
      expand_keys: true <4>

processors: <5>
  - add_host_metadata: ~
  - add_cloud_metadata: ~
  - add_docker_metadata: ~
  - add_kubernetes_metadata: ~
```

1. Use the filestream input to read lines from active log files.
2. Values from the decoded JSON object overwrite the fields that {{filebeat}} normally adds (type, source, offset, etc.) in case of conflicts.
3. {{filebeat}} adds an "error.message" and "error.type: json" key in case of JSON unmarshalling errors.
4. {{filebeat}} will recursively de-dot keys in the decoded JSON, and expand them into a hierarchical object structure.
5. Processors enhance your data. See [processors](beats://docs/reference/filebeat/filtering-enhancing-data.md) to learn more.


For Filebeat < 7.16

```yaml
filebeat.inputs:
- type: log
  paths: /path/to/logs.json
  json.keys_under_root: true
  json.overwrite_keys: true
  json.add_error_key: true
  json.expand_keys: true

processors:
- add_host_metadata: ~
- add_cloud_metadata: ~
- add_docker_metadata: ~
- add_kubernetes_metadata: ~
```
::::::

::::::{tab-item} Kubernetes
1. Make sure your application logs to stdout/stderr.
2. Follow the [Run Filebeat on Kubernetes](beats://docs/reference/filebeat/running-on-kubernetes.md) guide.
3. Enable [hints-based autodiscover](beats://docs/reference/filebeat/configuration-autodiscover-hints.md) (uncomment the corresponding section in `filebeat-kubernetes.yaml`).
4. Add these annotations to your pods that log using ECS loggers. This will make sure the logs are parsed appropriately.

```yaml
annotations:
  co.elastic.logs/json.overwrite_keys: true <1>
  co.elastic.logs/json.add_error_key: true <2>
  co.elastic.logs/json.expand_keys: true <3>
```

1. Values from the decoded JSON object overwrite the fields that {{filebeat}} normally adds (type, source, offset, etc.) in case of conflicts.
2. {{filebeat}} adds an "error.message" and "error.type: json" key in case of JSON unmarshalling errors.
3. {{filebeat}} will recursively de-dot keys in the decoded JSON, and expand them into a hierarchical object structure.
::::::

::::::{tab-item} Docker
1. Make sure your application logs to stdout/stderr.
2. Follow the [Run Filebeat on Docker](beats://docs/reference/filebeat/running-on-docker.md) guide.
3. Enable [hints-based autodiscover](beats://docs/reference/filebeat/configuration-autodiscover-hints.md).
4. Add these labels to your containers that log using ECS loggers. This will make sure the logs are parsed appropriately.

```yaml
labels:
  co.elastic.logs/json.overwrite_keys: true <1>
  co.elastic.logs/json.add_error_key: true <2>
  co.elastic.logs/json.expand_keys: true <3>
```

1. Values from the decoded JSON object overwrite the fields that {{filebeat}} normally adds (type, source, offset, etc.) in case of conflicts.
2. {{filebeat}} adds an "error.message" and "error.type: json" key in case of JSON unmarshalling errors.
3. {{filebeat}} will recursively de-dot keys in the decoded JSON, and expand them into a hierarchical object structure.
::::::

:::::::
For more information, see the [Filebeat reference](beats://docs/reference/filebeat/configuring-howto-filebeat.md).

