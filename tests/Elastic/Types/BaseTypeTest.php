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
 * Test: BaseType
 *
 * @version v1.x
 *
 * @see Elastic\Types\BaseType
 *
 * @author Philip Krauss <philip.krauss@elastic.co>
 */
class BaseTypeTest extends BaseTestCase
{

    /**
     * @covers Elastic\Types\BaseType::toArray
     */
    public function testToArray()
    {
        $tracing = new Tracing($this->generateTraceId(), $this->generateTransactionId());
        $this->assertInstanceOf(BaseType::class, $tracing);

        $arr1 = $tracing->toArray();
        $arr2 = $tracing->jsonSerialize();

        $this->assertEquals($arr1, $arr2);
    }

    /**
     * @covers Elastic\Types\BaseType::__toString
     */
    public function testToString()
    {
        $tracing = new Tracing($this->generateTraceId(), $this->generateTransactionId());
        $this->assertInstanceOf(BaseType::class, $tracing);

        $json1 = json_encode($tracing);
        $json2 = $tracing->__toString();

        $this->assertEquals($json1, $json2);
    }
}
