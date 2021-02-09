<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

// Licensed to Elasticsearch B.V under one or more agreements.
// Elasticsearch B.V licenses this file to you under the Apache 2.0 License.
// See the LICENSE file in the project root for more information

namespace Elastic\Tests\Monolog\Formatter;

use DateTimeImmutable;
use DateTimeZone;
use Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter;
use Elastic\Tests\BaseTestCase;
use Elastic\Tests\HelperForMonolog;
use Elastic\Types\{Error, Service, Tracing, User};
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\TagProcessor;
use Throwable;

/**
 * Test: ElasticCommonSchemaFormatter
 *
 * @see    https://www.elastic.co/guide/en/ecs/1.2/ecs-log.html
 * @see    \Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter
 *
 * @author Philip Krauss <philip.krauss@elastic.co>
 */
class ElasticCommonSchemaFormatterTest extends BaseTestCase
{
    public const ECS_VERSION = '1.2.0';

    /**
     * @covers \Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::__construct
     * @covers \Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::format
     */
    public function testFormat()
    {
        $msg = [
            'level'      => Logger::INFO,
            'level_name' => 'INFO',
            'channel'    => 'ecs',
            'datetime'   => new DateTimeImmutable("@0"),
            'message'    => md5(uniqid()),
            'context'    => [],
            'extra'      => [],
        ];

        $formatter = new ElasticCommonSchemaFormatter();
        $doc = $formatter->format($msg);

        // Must be a string terminated by a new line
        $this->assertIsString($doc);
        $this->assertStringEndsWith("\n", $doc);

        // Comply to the ECS format
        $decoded = json_decode($doc, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('@timestamp', $decoded);
        $this->assertArrayHasKey('log.level', $decoded);
        $this->assertArrayHasKey('log', $decoded);
        $this->assertArrayHasKey('logger', $decoded['log']);
        $this->assertArrayHasKey('message', $decoded);

        // Not other keys are set for the MVP
        $this->assertEquals(['@timestamp', 'log.level', 'message', 'ecs.version', 'log'], array_keys($decoded));
        $this->assertEquals(['logger'], array_keys($decoded['log']));

        // Values correctly propagated
        $this->assertEquals('1970-01-01T00:00:00.000000+00:00', $decoded['@timestamp']);
        $this->assertEquals($msg['level_name'], $decoded['log.level']);
        $this->assertEquals($msg['message'], $decoded['message']);
        $this->assertEquals(self::ECS_VERSION, $decoded['ecs.version']);
        $this->assertEquals($msg['channel'], $decoded['log']['logger']);
    }

    public function testTimezone()
    {
        $msg = [
            'level'      => Logger::INFO,
            'level_name' => 'INFO',
            'channel'    => 'ecs',
            'datetime'   => new DateTimeImmutable('2013-11-28T12:34:56.98765', new DateTimeZone('-03:45')),
            'message'    => md5(uniqid()),
            'context'    => [],
            'extra'      => [],
        ];

        $formatter = new ElasticCommonSchemaFormatter();
        $doc = $formatter->format($msg);

        // Comply to the ECS format
        $decoded = json_decode($doc, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('@timestamp', $decoded);
        $this->assertEquals('2013-11-28T12:34:56.987650-03:45', $decoded['@timestamp']);
    }

    /**
     * @depends testFormat
     *
     * @covers  \Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::__construct
     * @covers  \Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::format
     */
    public function testContextWithTracing()
    {
        $tracing = new Tracing($this->generateTraceId(), $this->generateTransactionId());
        $msg = [
            'level'      => Logger::NOTICE,
            'level_name' => 'NOTICE',
            'channel'    => 'ecs',
            'datetime'   => new DateTimeImmutable("@0"),
            'message'    => md5(uniqid()),
            'context'    => ['tracing' => $tracing],
            'extra'      => [],
        ];

        $formatter = new ElasticCommonSchemaFormatter();
        $doc = $formatter->format($msg);

        $decoded = json_decode($doc, true);
        $this->assertArrayHasKey('trace', $decoded);
        $this->assertArrayHasKey('transaction', $decoded);
        $this->assertArrayHasKey('id', $decoded['trace']);
        $this->assertArrayHasKey('id', $decoded['transaction']);

        $this->assertEquals($tracing->toArray()['trace']['id'], $decoded['trace']['id']);
        $this->assertEquals($tracing->toArray()['transaction']['id'], $decoded['transaction']['id']);
    }

    /**
     * @depends testFormat
     *
     * @covers  \Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::__construct
     * @covers  \Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::format
     */
    public function testContextWithService()
    {
        $service = new Service();
        $service->setId(rand(100, 999));
        $service->setName('funky-service-01');

        $msg = [
            'level'      => Logger::NOTICE,
            'level_name' => 'NOTICE',
            'channel'    => 'ecs',
            'datetime'   => new DateTimeImmutable("@0"),
            'message'    => md5(uniqid()),
            'context'    => ['service' => $service],
            'extra'      => [],
        ];

        $formatter = new ElasticCommonSchemaFormatter();
        $doc = $formatter->format($msg);

        $decoded = json_decode($doc, true);
        $this->assertArrayHasKey('service', $decoded);
        $this->assertArrayHasKey('id', $decoded['service']);
        $this->assertArrayHasKey('name', $decoded['service']);

        $this->assertEquals($service->toArray()['service']['id'], $decoded['service']['id']);
        $this->assertEquals($service->toArray()['service']['name'], $decoded['service']['name']);
    }

    /**
     * @depends testFormat
     *
     * @covers  \Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::__construct
     * @covers  \Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::format
     */
    public function testContextWithUser()
    {
        $user = new User();
        $user->setId(rand(100, 999));
        $user->setHash(md5(uniqid()));

        $msg = [
            'level'      => Logger::NOTICE,
            'level_name' => 'NOTICE',
            'channel'    => 'ecs',
            'datetime'   => new DateTimeImmutable("@0"),
            'message'    => md5(uniqid()),
            'context'    => ['user' => $user],
            'extra'      => [],
        ];

        $formatter = new ElasticCommonSchemaFormatter();
        $doc = $formatter->format($msg);

        $decoded = json_decode($doc, true);
        $this->assertArrayHasKey('user', $decoded);
        $this->assertArrayHasKey('id', $decoded['user']);
        $this->assertArrayHasKey('hash', $decoded['user']);

        $this->assertEquals($user->toArray()['user']['id'], $decoded['user']['id']);
        $this->assertEquals($user->toArray()['user']['hash'], $decoded['user']['hash']);
    }

    /**
     * @return array<array<mixed>>
     */
    public function dataProviderForTestContextWithError(): iterable
    {
        return [
            [self::generateException(), false],
            [self::generateException(), true],
        ];
    }

    /**
     * @depends      testFormat
     *
     * @dataProvider dataProviderForTestContextWithError
     *
     * @covers       \Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::__construct
     * @covers       \Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::format
     *
     * @param Throwable $throwable
     * @param bool      $shouldWrap
     */
    public function testContextWithError(Throwable $throwable, bool $shouldWrap): void
    {
        $msg = [
            'level'      => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel'    => 'ecs',
            'datetime'   => new DateTimeImmutable("@0"),
            'message'    => md5(uniqid()),
            'context'    => ['error' => $shouldWrap ? new Error($throwable) : $throwable],
            'extra'      => [],
        ];

        $formatter = new ElasticCommonSchemaFormatter();
        $doc = $formatter->format($msg);
        $decoded = json_decode($doc, true);

        // ECS Struct ?
        $this->assertArrayHasKey('error', $decoded);
        $this->assertArrayHasKey('type', $decoded['error']);
        $this->assertArrayHasKey('message', $decoded['error']);
        $this->assertArrayHasKey('code', $decoded['error']);
        $this->assertArrayHasKey('stack_trace', $decoded['error']);

        // Ensure Array merging is sound ..
        $this->assertArrayHasKey('log.level', $decoded);
        $this->assertArrayHasKey('logger', $decoded['log']);

        // Values Correct ?
        $this->assertEquals('InvalidArgumentException', $decoded['error']['type']);
        $this->assertEquals($throwable->getMessage(), $decoded['error']['message']);
        $this->assertEquals($throwable->getCode(), $decoded['error']['code']);
        $this->assertSame($throwable->__toString(), $decoded['error']['stack_trace']);

        // Throwable removed from Context/Labels ?
        $this->assertArrayNotHasKey('labels', $decoded);
    }

    /**
     * @depends testFormat
     *
     * @covers  \Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::__construct
     */
    public function testTags()
    {
        $msg = [
            'level'      => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel'    => 'ecs',
            'datetime'   => new DateTimeImmutable("@0"),
            'message'    => md5(uniqid()),
            'context'    => [],
            'extra'      => [],
        ];

        $tags = [
            'one',
            'two',
        ];

        $formatter = new ElasticCommonSchemaFormatter($tags);
        $doc = $formatter->format($msg);

        $decoded = json_decode($doc, true);
        $this->assertArrayHasKey('tags', $decoded);
        $this->assertEquals($tags, $decoded['tags']);
    }

    private static function isPrefixOf(string $prefix, string $text, bool $isCaseSensitive = true): bool
    {
        $prefixLen = strlen($prefix);
        if ($prefixLen === 0) {
            return true;
        }

        $substrCompareRetVal = substr_compare(
            $text /* <- haystack */,
            $prefix /* <- needle */,
            0 /* <- offset */,
            $prefixLen /* <- length */,
            !$isCaseSensitive /* <- case_insensitivity */
        );

        return $substrCompareRetVal === 0;
    }

    /**
     * @depends testFormat
     *
     * @covers  \Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::__construct
     * @covers  \Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::format
     */
    public function testSanitizeOfLabelKeys()
    {
        $inLabels = [
            'sim ple' => 'sim_ple',
            ' lpad'   => 'lpad',
            'rpad '   => 'rpad',
            'foo.bar' => 'foo_bar',
            'a.b.c'   => 'a_b_c',
            '.hello'  => '_hello',
            'lorem.'  => 'lorem_',
            'st*ar'   => 'st_ar',
            'sla\sh'  => 'sla_sh',
            'a.b*c\d' => 'a_b_c_d',
        ];

        $inContext = ['labels' => $inLabels];
        foreach ($inLabels as $key => $val) {
            $inContext['top_level_' . $key] = $key;
        }

        $inRecord = [
            'level'      => Logger::NOTICE,
            'level_name' => 'NOTICE',
            'channel'    => 'ecs',
            'datetime'   => new DateTimeImmutable("@0"),
            'message'    => md5(uniqid()),
            'context'    => $inContext,
            'extra'      => [],
        ];

        $formatter = new ElasticCommonSchemaFormatter();
        $doc = $formatter->format($inRecord);
        $decoded = json_decode($doc, true);

        $this->assertArrayHasKey('labels', $decoded);
        $outLabels = $decoded['labels'];
        $this->assertCount(count($inLabels), $outLabels);
        foreach ($inLabels as $keyPrevious => $keySanitized) {
            $this->assertArrayNotHasKey($keyPrevious, $outLabels, $keyPrevious);
            $this->assertArrayHasKey($keySanitized, $outLabels, $keySanitized);
        }

        $topLevelFoundCount = 0;
        foreach ($inContext as $key => $val) {
            if (!self::isPrefixOf('top_level_', $key)) {
                continue;
            }

            $this->assertSame('top_level_' . $val, $key);
            ++$topLevelFoundCount;
        }
        $this->assertSame(count($inLabels), $topLevelFoundCount);
    }

    public function testTagProcessor()
    {
        $testHelper = new TestHelper();
        $testHelper->expectedAdditionalTopLevelKeys = ['tags'];
        $testHelper->expectedLogLevel = Logger::ALERT;

        $decodedJson = $testHelper->run(
            function (Logger $logger) use ($testHelper) {
                $logger->pushProcessor(new TagProcessor(['tag_key' => 'tag_val', 'tag_val_without_key']));
                $logger->alert($testHelper->expectedMessage);
            }
        );

        $tags = $decodedJson['tags'];
        self::assertCount(2, $tags);
        self::assertSame('tag_val', $tags['tag_key']);
        self::assertSame('tag_val_without_key', $tags[0]);
    }

    public function testServiceNamePerLogger()
    {
        $testHelper = new TestHelper();
        $testHelper->expectedAdditionalTopLevelKeys = ['service.name'];
        $testHelper->expectedLogLevel = Logger::WARNING;

        $decodedJson = $testHelper->run(
            function (Logger $logger) use ($testHelper) {
                $logger->pushProcessor(
                    function ($record) {
                        $record['extra']['service.name'] = 'my_service';
                        return $record;
                    }
                );
                $logger->warning($testHelper->expectedMessage);
            }
        );

        self::assertSame('my_service', $decodedJson['service.name']);
    }

    /**
     * @return array<array<bool>>
     */
    public function dataProviderForTestsIntrospectionProcessor(): iterable
    {
        return [[false], [true]];
    }

    /**
     * @dataProvider dataProviderForTestsIntrospectionProcessor
     *
     * @param bool $useLogOriginFromContext
     */
    public function testsIntrospectionProcessor(bool $useLogOriginFromContext)
    {
        $testHelper = new TestHelper();
        if (!$useLogOriginFromContext) {
            $testHelper->adaptFormatter = function (ElasticCommonSchemaFormatter $formatter) {
                $formatter->useLogOriginFromContext(false);
            };
        }
        $testHelper->expectedLogLevel = Logger::EMERGENCY;

        if ($useLogOriginFromContext) {
            $testHelper->expectedAdditionalLogKeys = ['origin'];
        } else {
            $testHelper->expectedAdditionalTopLevelKeys = ['file', 'line', 'class', 'function'];
        }

        $logOrigin = [];
        $decodedJson = $testHelper->run(
            function (Logger $logger) use ($testHelper, &$logOrigin) {
                $logger->pushProcessor(new IntrospectionProcessor());
                // Unfortunately IntrospectionProcessor removes all the stack frames with 'Monolog\' in the namespace
                // even if 'Monolog\' is in the middle of the namespace
                // so we use a helper class without 'Monolog\' in its namespace to work around that limitation
                HelperForMonolog::logEmergency($logger, $testHelper->expectedMessage, /* ref */ $logOrigin);
            }
        );

        if ($useLogOriginFromContext) {
            self::assertSame($logOrigin['file'], $decodedJson['log']['origin']['file']['name']);
            self::assertSame($logOrigin['line'], $decodedJson['log']['origin']['file']['line']);
            self::assertSame($logOrigin['class'] . '::' . $logOrigin['function'], $decodedJson['log']['origin']['function']);
        } else {
            self::assertArrayNotHasKey('origin', $decodedJson['log']);
        }
    }
}
