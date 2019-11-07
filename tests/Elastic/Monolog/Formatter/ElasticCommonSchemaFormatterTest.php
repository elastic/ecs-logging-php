<?php declare(strict_types=1);

// Licensed to Elasticsearch B.V under one or more agreements.
// Elasticsearch B.V licenses this file to you under the Apache 2.0 License.
// See the LICENSE file in the project root for more information

namespace Elastic\Tests\Monolog\Formatter;

use \Elastic\Tests\BaseTestCase;
use Monolog\Logger;
use Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter;

use Throwable;

/**
 * Test: ElasticCommonSchemaFormatter
 *
 * @version ECS v1.2.0
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
    public function testDistributedTracingWithOnlyTraceId()
    {
        $msg = [
            'level'      => Logger::NOTICE,
            'level_name' => 'NOTICE',
            'channel'    => 'ecs',
            'datetime'   => new \DateTimeImmutable("@0"),
            'message'    => md5(uniqid()),
            'context'    => ['trace' => $this->generateTraceId()],
            'extra'      => [],
        ];

        $formatter = new ElasticCommonSchemaFormatter();
        $doc = $formatter->format($msg);

        $decoded = json_decode($doc, true);
        $this->assertArrayHasKey('trace', $decoded);
        $this->assertArrayNotHasKey('transaction', $decoded);
        $this->assertArrayHasKey('id', $decoded['trace']);
        $this->assertEquals($msg['context']['trace'], $decoded['trace']['id']);
    }

    /**
     * @depends testDistributedTracingWithOnlyTraceId
     *
     * @covers Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::__construct
     * @covers Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::format
     */
    public function testDistributedTracingWithTraceAndTransactionId()
    {
        $msg = [
            'level'      => Logger::NOTICE,
            'level_name' => 'NOTICE',
            'channel'    => 'ecs',
            'datetime'   => new \DateTimeImmutable("@0"),
            'message'    => md5(uniqid()),
            'context'    => [
                'trace'       => $this->generateTraceId(),
                'transaction' => $this->generateTransactionId(),
            ],
            'extra'      => [],
        ];

        $formatter = new ElasticCommonSchemaFormatter();
        $doc = $formatter->format($msg);

        $decoded = json_decode($doc, true);
        $this->assertArrayHasKey('trace', $decoded);
        $this->assertArrayHasKey('transaction', $decoded);
        $this->assertArrayHasKey('id', $decoded['trace']);
        $this->assertArrayHasKey('id', $decoded['transaction']);

        $this->assertEquals($msg['context']['trace'], $decoded['trace']['id']);
        $this->assertEquals($msg['context']['transaction'], $decoded['transaction']['id']);
    }

    /**
     * @depends testDistributedTracingWithTraceAndTransactionId
     *
     * @covers Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::__construct
     * @covers Elastic\Monolog\Formatter\ElasticCommonSchemaFormatter::format
     */
    public function testDistributedTracingWithOnlyTransactionId()
    {
        $msg = [
            'level'      => Logger::NOTICE,
            'level_name' => 'NOTICE',
            'channel'    => 'ecs',
            'datetime'   => new \DateTimeImmutable("@0"),
            'message'    => md5(uniqid()),
            'context'    => ['transaction' => $this->generateTransactionId()],
            'extra'      => [],
        ];

        $formatter = new ElasticCommonSchemaFormatter();
        $doc = $formatter->format($msg);

        $decoded = json_decode($doc, true);

        // Trace is required, not tracing options set
        // but transaction is in the context
        $this->assertArrayNotHasKey('trace', $decoded);
        $this->assertArrayNotHasKey('transaction', $decoded);
        $this->assertArrayHasKey('transaction', $decoded['labels']);
        $this->assertEquals($decoded['labels']['transaction'], $msg['context']['transaction']);
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
        $this->assertEquals(42, $decoded['log']['origin']['file']['line']);

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
