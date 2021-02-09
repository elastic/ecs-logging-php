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
    public $adaptFormatter = null;

    /** @var int */
    public $expectedLogLevel;

    /** @var string */
    public $expectedMessage = 'My log message';

    /** @var array */
    public $expectedContext = [];

    /** @var array */
    public $expectedAdditionalTopLevelKeys = [];

    /** @var array */
    public $expectedAdditionalLogKeys = [];

    public function run(Closure $log): array
    {
        $this->logOriginFunction = __METHOD__;

        $logger = new Logger($this->loggerName);
        $handler = new MockHandler();

        $formatter = new ElasticCommonSchemaFormatter();
        if ($this->adaptFormatter !== null) {
            ($this->adaptFormatter)($formatter);
        }
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);

        $timeBefore = new DateTimeImmutable();
        $log($logger);
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

        TestCase::assertEquals(Logger::getLevelName($this->expectedLogLevel), $decodedJson['log.level']);

        TestCase::assertEquals(ElasticCommonSchemaFormatterTest::ECS_VERSION, $decodedJson['ecs.version']);

        TestCase::assertEquals($this->expectedMessage, $decodedJson['message']);

        $expectedLogKeys = array_merge(['logger'], $this->expectedAdditionalLogKeys);
        TestCase::assertEquals($expectedLogKeys, array_keys($decodedJson['log']));
        TestCase::assertSame($this->loggerName, $decodedJson['log']['logger']);

        foreach ($this->expectedContext as $ctxKey => $ctxVal) {
            TestCase::assertArrayHasKey($ctxKey, $decodedJson[0]);
        }

        return $decodedJson;
    }
}
