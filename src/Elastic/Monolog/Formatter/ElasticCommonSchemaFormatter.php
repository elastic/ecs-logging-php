<?php

declare(strict_types=1);

// Licensed to Elasticsearch B.V under one or more agreements.
// Elasticsearch B.V licenses this file to you under the Apache 2.0 License.
// See the LICENSE file in the project root for more information

namespace Elastic\Monolog\Formatter;

use Monolog\Formatter\NormalizerFormatter;
use Throwable;
use Elastic\Types\Error as EcsError;

/**
 * Serializes a log message to the Elastic Common Schema (ECS)
 *
 * @version Monolog v2.x
 * @version ECS v1.x
 *
 * @see     https://www.elastic.co/guide/en/ecs/1.4/ecs-log.html
 * @see     \Elastic\Tests\Monolog\Formatter\ElasticCommonSchemaFormatterTest
 *
 * @author  Philip Krauss <philip.krauss@elastic.co>
 */
class ElasticCommonSchemaFormatter extends NormalizerFormatter
{
    private const ECS_VERSION = '1.2.0';

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

    /** @inheritDoc */
    protected function normalize($data, int $depth = 0)
    {
        if ($depth > $this->maxNormalizeDepth) {
            return parent::normalize($data, $depth);
        }

        if ($data instanceof Throwable) {
            return EcsError::serialize($data);
        }

        if ($data instanceof EcsError) {
            return $data->jsonSerialize();
        }

        return parent::normalize($data, $depth);
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
        $inRecord = $this->normalize($record);

        // Build Skeleton with "@timestamp" and "log.level"
        $outRecord = [
            '@timestamp' => $inRecord['datetime'],
            'log.level'  => $inRecord['level_name'],
        ];

        // Add "message"
        if (isset($inRecord['message']) === true) {
            $outRecord['message'] = $inRecord['message'];
        }

        // Add "ecs.version"
        $outRecord['ecs.version'] = self::ECS_VERSION;

        // Add "log": { "logger": ..., ... }
        $outRecord['log'] = [
            'logger' => $inRecord['channel'],
        ];

        // Add Tracing Context
        if (isset($inRecord['context']['tracing']['Elastic\Types\Tracing']) === true) {
            $outRecord += $inRecord['context']['tracing']['Elastic\Types\Tracing'];
            unset($inRecord['context']['tracing']);
        }

        // Add Service Context
        if (isset($inRecord['context']['service']['Elastic\Types\Service']) === true) {
            $outRecord += $inRecord['context']['service']['Elastic\Types\Service'];
            unset($inRecord['context']['service']);
        }

        // Add User Context
        if (isset($inRecord['context']['user']['Elastic\Types\User']) === true) {
            $outRecord += $inRecord['context']['user']['Elastic\Types\User'];
            unset($inRecord['context']['user']);
        }

        self::formatContext($inRecord['extra'], /* ref */ $outRecord);
        self::formatContext($inRecord['context'], /* ref */ $outRecord);

        // Add ECS Tags
        if (empty($this->tags) === false) {
            $outRecord['tags'] = $this->normalize($this->tags);
        }

        return $this->toJson($outRecord) . "\n";
    }

    private static function formatContext(array $inContext, array &$outRecord): void
    {
        // Context should go to the top of the out record
        foreach ($inContext as $contextKey => $contextVal) {
            // label keys should be sanitized
            if ($contextKey === 'labels') {
                $outLabels = [];
                foreach ($contextVal as $labelKey => $labelVal) {
                    $outLabels[str_replace(['.', ' ', '*', '\\'], '_', trim($labelKey))] = $labelVal;
                }
                $outRecord['labels'] = $outLabels;
                continue;
            }

            $outRecord[$contextKey] = $contextVal;
        }
    }
}
