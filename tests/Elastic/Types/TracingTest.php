<?php

declare(strict_types=1);

// Licensed to Elasticsearch B.V under one or more agreements.
// Elasticsearch B.V licenses this file to you under the Apache 2.0 License.
// See the LICENSE file in the project root for more information

namespace Elastic\Tests\Types;

use \Elastic\Tests\BaseTestCase;
use Elastic\Types\Tracing;
use Elastic\Types\BaseType;

/**
 * Test: Tracing (Type)
 *
 * @version v1.x
 *
 * @see Elastic\Types\Tracing
 *
 * @author Philip Krauss <philip.krauss@elastic.co>
 */
class TracingTest extends BaseTestCase
{

    /**
     * @covers Elastic\Types\Tracing::__construct
     * @covers Elastic\Types\Tracing::jsonSerialize
     */
    public function testSerialization()
    {
        $traceId = $this->generateTraceId();
        $trxId   = $this->generateTransactionId();

        $tracing = new Tracing($traceId, $trxId);
        $this->assertInstanceOf(BaseType::class, $tracing);

        $json = json_encode($tracing);

        // Comply to the ECS format
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('trace', $decoded);
        $this->assertArrayHasKey('transaction', $decoded);
        $this->assertArrayHasKey('id', $decoded['trace']);
        $this->assertArrayHasKey('id', $decoded['transaction']);

        // Values correctly propagated
        $this->assertEquals($traceId, $decoded['trace']['id']);
        $this->assertEquals($trxId, $decoded['transaction']['id']);
    }

    /**
     * @depends testSerialization
     *
     * @covers Elastic\Types\Tracing::__construct
     * @covers Elastic\Types\Tracing::jsonSerialize
     */
    public function testMvpWithoutTransactionId()
    {
        $traceId = $this->generateTraceId();

        $tracing = new Tracing($traceId);
        $json = json_encode($tracing);

        $decoded = json_decode($json, true);
        $this->assertArrayNotHasKey('transaction', $decoded);
        $this->assertArrayHasKey('trace', $decoded);
        $this->assertArrayHasKey('id', $decoded['trace']);
        $this->assertEquals($traceId, $decoded['trace']['id']);
    }
}
