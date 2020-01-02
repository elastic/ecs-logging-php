<?php

declare(strict_types=1);

// Licensed to Elasticsearch B.V under one or more agreements.
// Elasticsearch B.V licenses this file to you under the Apache 2.0 License.
// See the LICENSE file in the project root for more information

namespace Elastic\Tests\Types;

use \Elastic\Tests\BaseTestCase;
use Elastic\Types\Error;
use Elastic\Types\BaseType;

/**
 * Test: Error (Type)
 *
 * @version v1.x
 *
 * @see Elastic\Types\Error
 *
 * @author Philip Krauss <philip.krauss@elastic.co>
 */
class ErrorTest extends BaseTestCase
{

    /**
     * @covers Elastic\Types\Error::__construct
     * @covers Elastic\Types\Error::jsonSerialize
     */
    public function testSerialization()
    {
        $t = $this->generateException();
        $error = new Error($t);

        $this->assertInstanceOf(BaseType::class, $error);

        // Comply to the ECS format
        $decoded = $error->toArray();
        $this->assertIsArray($decoded);

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

        // Values Correct ?
        $this->assertEquals('BaseTestCase.php', basename($decoded['log']['origin']['file']['name']));
        $this->assertEquals(44, $decoded['log']['origin']['file']['line']);

        $this->assertEquals('InvalidArgumentException', $decoded['error']['type']);
        $this->assertEquals($t->getMessage(), $decoded['error']['message']);
        $this->assertEquals($t->getCode(), $decoded['error']['code']);
        $this->assertIsArray($decoded['error']['stack_trace']);
        $this->assertNotEmpty($decoded['error']['stack_trace']);
    }
}
