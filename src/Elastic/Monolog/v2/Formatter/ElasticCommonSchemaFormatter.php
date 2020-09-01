<?php

declare(strict_types=1);

// Licensed to Elasticsearch B.V under one or more agreements.
// Elasticsearch B.V licenses this file to you under the Apache 2.0 License.
// See the LICENSE file in the project root for more information

namespace Elastic\Monolog\v2\Formatter;

use Monolog\Formatter\NormalizerFormatter;
use Elastic\Types\{Tracing, User, Service};
use Throwable;

/**
 * Serializes a log message to the Elastic Common Schema (ECS)
 *
 * @version Monolog v2.x
 * @version ECS v1.x
 *
 * @see https://www.elastic.co/guide/en/ecs/1.4/ecs-log.html
 * @see Elastic\Monolog\v2\Formatter\ElasticCommonSchemaFormatterTest
 *
 * @author Philip Krauss <philip.krauss@elastic.co>
 */
class ElasticCommonSchemaFormatter extends NormalizerFormatter
{

    /**
     * @var array
     *
     * @link https://www.elastic.co/guide/en/ecs/current/ecs-base.html
     */
    protected $tags;

    /**
     * @param array $tags optional tags to enrich the log lines
     */
    public function __construct(array $tags = [])
    {
        parent::__construct('Y-m-d\TH:i:s.uP');
        $this->tags = $tags;
    }

    /**
     * {@inheritdoc}
     *
     * @link https://www.elastic.co/guide/en/ecs/1.1/ecs-log.html
     * @link https://www.elastic.co/guide/en/ecs/1.1/ecs-base.html
     * @link https://www.elastic.co/guide/en/ecs/current/ecs-tracing.html
     */
    public function format(array $record): string
    {
        $record = $this->normalize($record);

        // Build Skeleton
        $message = [
            '@timestamp' => $record['datetime'],
            'log'        => [
                'level'  => $record['level_name'],
                'logger' => $record['channel'],
            ],
        ];

        // Add Error Context
        if (isset($record['context']['error']['Elastic\Types\Error']) === true) {
            $message['error'] = $record['context']['error']['Elastic\Types\Error']['error'];
            $message['log']   = array_merge($message['log'], $record['context']['error']['Elastic\Types\Error']['log']);

            $record['message'] ?? $message['error']['message'];
            unset($record['context']['error']);
        }

        // Add Log Message
        if (isset($record['message']) === true) {
            $message['message'] = $record['message'];
        }

        // Add Tracing Context
        if (isset($record['context']['tracing']['Elastic\Types\Tracing']) === true) {
            $message += $record['context']['tracing']['Elastic\Types\Tracing'];
            unset($record['context']['tracing']);
        }

        // Add Service Context
        if (isset($record['context']['service']['Elastic\Types\Service']) === true) {
            $message += $record['context']['service']['Elastic\Types\Service'];
            unset($record['context']['service']);
        }

        // Add User Context
        if (isset($record['context']['user']['Elastic\Types\User']) === true) {
            $message += $record['context']['user']['Elastic\Types\User'];
            unset($record['context']['user']);
        }

        // Add ECS Labels
        if (empty($record['context']) === false) {
            $message['labels'] = [];
            foreach ($record['context'] as $key => $val) {
                $message['labels'][str_replace(['.', ' ', '*', '\\'], '_', trim($key))] = $val;
            }
        }

        // Add ECS Tags
        if (empty($this->tags) === false) {
            $message['tags'] = $this->normalize($this->tags);
        }

        return $this->toJson($message) . "\n";
    }
}
