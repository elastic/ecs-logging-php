<?php

declare(strict_types=1);

// Licensed to Elasticsearch B.V under one or more agreements.
// Elasticsearch B.V licenses this file to you under the Apache 2.0 License.
// See the LICENSE file in the project root for more information

namespace Elastic\Tests\Types;

use Elastic\Tests\BaseTestCase;
use Elastic\Types\BaseType;
use Elastic\Types\Error;

/**
 * Test: Error (Type)
 *
 * @version v1.x
 *
 * @see     \Elastic\Types\Error
 *
 * @author  Philip Krauss <philip.krauss@elastic.co>
 */
class ErrorTest extends BaseTestCase
{

    /**
     * @covers \Elastic\Types\Error::__construct
     * @covers \Elastic\Types\Error::jsonSerialize
     */
    public function testSerialization()
    {
        $t = $this->generateException();
        $error = new Error($t);

        $this->assertInstanceOf(BaseType::class, $error);

        // Comply to the ECS format
        $decoded = $error->toArray();
        $this->assertIsArray($decoded);

        $this->assertArrayHasKey('type', $decoded);
        $this->assertArrayHasKey('message', $decoded);
        $this->assertArrayHasKey('code', $decoded);
        $this->assertArrayHasKey('stack_trace', $decoded);

        // Values Correct ?
        $this->assertEquals('InvalidArgumentException', $decoded['type']);
        $this->assertEquals($t->getMessage(), $decoded['message']);
        $this->assertEquals($t->getCode(), $decoded['code']);
        $this->assertSame($t->__toString(), $decoded['stack_trace']);
    }
}
