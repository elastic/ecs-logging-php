<?php declare(strict_types=1);

// Licensed to Elasticsearch B.V under one or more agreements.
// Elasticsearch B.V licenses this file to you under the Apache 2.0 License.
// See the LICENSE file in the project root for more information

namespace Elastic\Monolog\Formatter;

use Monolog\Formatter\NormalizerFormatter;

use Throwable;

/**
 * Serializes a log message to the Elastic Common Schema (ECS)
 *
 * @version v1.x
 *
 * @see https://www.elastic.co/guide/en/ecs/1.4/ecs-log.html
 * @see Elastic\Monolog\Formatter\ElasticCommonSchemaFormatterTest
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
        parent::__construct('Y-m-d\TH:i:s.u\Z');
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

        // Add Exception
        if (isset($record['context']['throwable']) === true) {
            if (isset($record['message']) === false) {
                $message['message'] = $record['context']['throwable']['error']['message'];
            }
            $message['error'] = $record['context']['throwable']['error'];
            $message['log']   = array_merge($message['log'], $record['context']['throwable']['log']);
            unset($record['context']['throwable']);
        }

        // Add Tracing Context
        if (isset($record['context']['trace']) === true) {
            $message['trace'] = ['id' => trim($record['context']['trace'])];
            unset($record['context']['trace']);

            if (isset($record['context']['transaction']) === true) {
                $message['transaction'] = ['id' => trim($record['context']['transaction'])];
                unset($record['context']['transaction']);
            }
        }

        // Add Log Message
        if (isset($record['message']) === true) {
            $message['message'] = $record['message'];
        }

        // Add ECS Labels
        if (empty($record['context']) === false) {
            $message['labels'] = [];
            foreach ($record['context'] as $key => $val) {
                $message['labels'][str_replace(['.', ' '], '_', trim($key))] = $val;
            }
        }

        // Add ECS Tags
        if (empty($this->tags) === false) {
            $message['tags'] = $this->normalize($this->tags);
        }

        return $this->toJson($message) . "\n";
    }

    /**
     * Normalize Exception and return ECS compliant formart
     *
     * @param Throwable $e
     * @param int $depth, Def: 0
     *
     * @return array
     */
    protected function normalizeException(Throwable $e, int $depth = 0)
    {
        $normalized = parent::normalizeException($e, $depth);
        return [
            'error'   => [
                'type'        => $normalized['class'],
                'message'     => $normalized['message'],
                'code'        => $normalized['code'],
                'stack_trace' => explode(PHP_EOL, $e->getTraceAsString()),
            ],
            'log'     => [
                'origin' => [
                    'file' => [
                        'name' => $e->getFile(),
                        'line' => $e->getLine(),
                    ],
                ],
            ],
        ];
    }
}
