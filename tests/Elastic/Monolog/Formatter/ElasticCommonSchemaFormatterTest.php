<?php

declare(strict_types=1);

// Licensed to Elasticsearch B.V under one or more agreements.
// Elasticsearch B.V licenses this file to you under the Apache 2.0 License.
// See the LICENSE file in the project root for more information

namespace Elastic\Tests\Monolog\Formatter;

use Monolog\Logger;
use \Elastic\Tests\BaseTestCase;
use Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter;
use Elastic\Types\{Tracing, User, Service};
use Throwable;

/**
 * Test: ElasticCommonSchemaFormatter
 *
 * @version v1.x
 *
 * @see https://www.elastic.co/guide/en/ecs/1.2/ecs-log.html
 * @see Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter
 *
 * @author Philip Krauss <philip.krauss@elastic.co>
 */
class ElasticCommonSchemaFormatterTest extends BaseTestCase
{

    /**
     * @covers Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::__construct
     * @covers Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::format
     */
    public function testFormat()
    {
        $msg = [
            'level'      => Logger::INFO,
            'level_name' => 'INFO',
            'channel'    => 'ecs',
            'datetime'   => new \DateTimeImmutable("@0"),
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
        $this->assertArrayHasKey('log', $decoded);
        $this->assertArrayHasKey('level', $decoded['log']);
        $this->assertArrayHasKey('logger', $decoded['log']);
        $this->assertArrayHasKey('message', $decoded);

        // Not other keys are set for the MVP
        $this->assertEquals(['@timestamp', 'log', 'message'], array_keys($decoded));
        $this->assertEquals(['level', 'logger'], array_keys($decoded['log']));

        // Values correctly propagated
        $this->assertEquals('1970-01-01T00:00:00.000000Z', $decoded['@timestamp']);
        $this->assertEquals($msg['level_name'], $decoded['log']['level']);
        $this->assertEquals($msg['channel'], $decoded['log']['logger']);
        $this->assertEquals($msg['message'], $decoded['message']);
    }

    /**
     * @depends testFormat
     *
     * @covers Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::__construct
     * @covers Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::format
     */
    public function testContextWithTracing()
    {
        $tracing = new Tracing($this->generateTraceId(), $this->generateTransactionId());
        $msg = [
            'level'      => Logger::NOTICE,
            'level_name' => 'NOTICE',
            'channel'    => 'ecs',
            'datetime'   => new \DateTimeImmutable("@0"),
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
     * @covers Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::__construct
     * @covers Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::format
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
            'datetime'   => new \DateTimeImmutable("@0"),
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
     * @covers Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::__construct
     * @covers Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::format
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
            'datetime'   => new \DateTimeImmutable("@0"),
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
     * @depends testFormat
     *
     * @covers Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::__construct
     */
    public function testTags()
    {
        $msg = [
            'level'      => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel'    => 'ecs',
            'datetime'   => new \DateTimeImmutable("@0"),
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

    /**
     * @depends testFormat
     *
     * @covers Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::__construct
     * @covers Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::normalizeException
     */
    public function testNormalizeException()
    {
        $t = $this->generateException();
        $msg = [
            'level'      => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel'    => 'ecs',
            'datetime'   => new \DateTimeImmutable("@0"),
            'message'    => md5(uniqid()),
            'context'    => ['throwable' => $t],
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

        $this->assertArrayHasKey('log', $decoded);
        $this->assertArrayHasKey('origin', $decoded['log']);
        $this->assertArrayHasKey('file', $decoded['log']['origin']);
        $this->assertArrayHasKey('name', $decoded['log']['origin']['file']);
        $this->assertArrayHasKey('line', $decoded['log']['origin']['file']);

        // Ensure Array merging is sound ..
        $this->assertArrayHasKey('level', $decoded['log']);
        $this->assertArrayHasKey('logger', $decoded['log']);

        // Values Correct ?
        $this->assertEquals('BaseTestCase.php', basename($decoded['log']['origin']['file']['name']));
        $this->assertEquals(44, $decoded['log']['origin']['file']['line']);

        $this->assertEquals('InvalidArgumentException', $decoded['error']['type']);
        $this->assertEquals($t->getMessage(), $decoded['error']['message']);
        $this->assertEquals($t->getCode(), $decoded['error']['code']);
        $this->assertIsArray($decoded['error']['stack_trace']);
        $this->assertNotEmpty($decoded['error']['stack_trace']);

        // Throwable removed from Context/Labels ?
        $this->assertArrayNotHasKey('labels', $decoded);
    }

    /**
     * @depends testFormat
     *
     * @covers Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::__construct
     * @covers Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::format
     */
    public function testSanitizeOfLabelKeys()
    {
        $msg = [
            'level'      => Logger::NOTICE,
            'level_name' => 'NOTICE',
            'channel'    => 'ecs',
            'datetime'   => new \DateTimeImmutable("@0"),
            'message'    => md5(uniqid()),
            'context'    => [
                'sim ple' => 'sim_ple',
                ' lpad'   => 'lpad',
                'rpad '   => 'rpad',
                'foo.bar' => 'foo_bar',
                'a.b.c'   => 'a_b_c',
                '.hello'  => '_hello',
                'lorem.'  => 'lorem_',
            ],
            'extra'      => [],
        ];

        $formatter = new ElasticCommonSchemaFormatter();
        $doc = $formatter->format($msg);
        $decoded = json_decode($doc, true);

        $this->assertArrayHasKey('labels', $decoded);
        foreach ($msg['context'] as $keyPrevious => $keySanitized) {
            $this->assertArrayNotHasKey($keyPrevious, $decoded['labels'], $keyPrevious);
            $this->assertArrayHasKey($keySanitized, $decoded['labels'], $keySanitized);
        }
    }
}
