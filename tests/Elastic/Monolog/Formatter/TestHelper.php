<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

// Licensed to Elasticsearch B.V under one or more agreements.
// Elasticsearch B.V licenses this file to you under the Apache 2.0 License.
// See the LICENSE file in the project root for more information

namespace Elastic\Tests\Monolog\Formatter;

use Closure;
use DateTimeImmutable;
use Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class TestHelper
{
    /** @var string */
    public $loggerName = 'MyLogger';

    /** @var Closure|null */
    public $adaptLogger = null;

    /** @var array */
    public $expectedAdditionalTopLevelKeys = [];

    public function run(int $logLevel, string $message, array $context = []): array
    {
        $logger = new Logger($this->loggerName);
        $handler = new MockHandler();
        $handler->setFormatter(new ElasticCommonSchemaFormatter());
        $logger->pushHandler($handler);
        if ($this->adaptLogger !== null) {
            ($this->adaptLogger)($logger);
        }

        $timeBefore = new DateTimeImmutable();
        $logger->addRecord($logLevel, $message, $context);
        $timeAfter = new DateTimeImmutable();

        TestCase::assertCount(1, $handler->records);
        TestCase::assertArrayHasKey('formatted', $handler->records[0]);
        $encodedJson = $handler->records[0]['formatted'];
        $decodedJson = json_decode($encodedJson, /* $associative */ true);
        TestCase::assertIsArray($decodedJson);

        $expectedTopLevelKeys = array_merge(
            ['@timestamp', 'log.level', 'message', 'ecs.version', 'log'],
            $this->expectedAdditionalTopLevelKeys
        );
        TestCase::assertEquals($expectedTopLevelKeys, array_keys($decodedJson));

        $timestamp = new DateTimeImmutable($decodedJson['@timestamp']);
        TestCase::assertGreaterThanOrEqual($timeBefore, $timestamp);
        TestCase::assertLessThanOrEqual($timeAfter, $timestamp);

        TestCase::assertEquals(Logger::getLevelName($logLevel), $decodedJson['log.level']);

        TestCase::assertEquals(ElasticCommonSchemaFormatterTest::ECS_VERSION, $decodedJson['ecs.version']);

        TestCase::assertEquals($message, $decodedJson['message']);

        TestCase::assertEquals(['logger'], array_keys($decodedJson['log']));
        TestCase::assertSame($this->loggerName, $decodedJson['log']['logger']);

        foreach ($context as $ctxKey => $ctxVal) {
            TestCase::assertArrayHasKey($ctxKey, $decodedJson[0]);
        }

        return $decodedJson;
    }
}
