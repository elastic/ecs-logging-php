<?php

declare(strict_types=1);

// Licensed to Elasticsearch B.V under one or more agreements.
// Elasticsearch B.V licenses this file to you under the Apache 2.0 License.
// See the LICENSE file in the project root for more information

namespace Elastic\Tests\Types;

use \Elastic\Tests\BaseTestCase;
use Elastic\Types\Service;
use Elastic\Types\BaseType;

/**
 * Test: User (Type)
 *
 * @version v1.x
 *
 * @see Elastic\Types\Service
 *
 * @author Philip Krauss <philip.krauss@elastic.co>
 */
class ServiceTest extends BaseTestCase
{

    /**
     * @covers Elastic\Types\Service::__construct
     * @covers Elastic\Types\Service::jsonSerialize
     * @covers Elastic\Types\Service::setId
     * @covers Elastic\Types\Service::setName
     * @covers Elastic\Types\Service::setState
     * @covers Elastic\Types\Service::setType
     * @covers Elastic\Types\Service::setVersion
     */
    public function testSerializationOfCoreFields()
    {
        $expected = [
            'id'      => rand(1000, 9999),
            'name'    => 'super-duper-app',
            'state'   => 'running',
            'type'    => sprintf('collector-%d', rand(1, 9)),
            'version' => sprintf('1.2.%d', rand(1, 99)),
        ];
        $service = new Service();
        $this->assertInstanceOf(BaseType::class, $service);

        $service->setId($expected['id']);
        $service->setName($expected['name']);
        $service->setState($expected['state']);
        $service->setType($expected['type']);
        $service->setVersion($expected['version']);

        $json = json_encode($service);

        // Comply to the ECS format
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('service', $decoded);
        $this->assertArrayHasKey('id', $decoded['service']);
        $this->assertArrayHasKey('name', $decoded['service']);
        $this->assertArrayHasKey('state', $decoded['service']);
        $this->assertArrayHasKey('type', $decoded['service']);
        $this->assertArrayHasKey('version', $decoded['service']);

        // Values correctly propagated
        $this->assertEquals($expected['id'], $decoded['service']['id']);
        $this->assertEquals($expected['name'], $decoded['service']['name']);
        $this->assertEquals($expected['state'], $decoded['service']['state']);
        $this->assertEquals($expected['type'], $decoded['service']['type']);
        $this->assertEquals($expected['version'], $decoded['service']['version']);
    }

    /**
     * @depends testSerializationOfCoreFields
     *
     * @covers Elastic\Types\Service::__construct
     * @covers Elastic\Types\Service::jsonSerialize
     * @covers Elastic\Types\Service::setEphemeralId
     */
    public function testSeralizeEphemeralId()
    {
        $expected = substr($this->generateTransactionId(), 0, 8);

        $service = new Service();
        $service->setEphemeralId($expected);

        $json = json_encode($service);

        $decoded = json_decode($json, true);
        $this->assertArrayHasKey('ephemeral_id', $decoded['service']);
        $this->assertEquals($expected, $decoded['service']['ephemeral_id']);
    }

    /**
     * @depends testSerializationOfCoreFields
     *
     * @covers Elastic\Types\Service::__construct
     * @covers Elastic\Types\Service::jsonSerialize
     * @covers Elastic\Types\Service::setNodeName
     */
    public function testSeralizeNodeName()
    {
        $expected = sprintf('instance-%d', rand(1000, 9999));

        $service = new Service();
        $service->setNodeName($expected);

        $json = json_encode($service);

        $decoded = json_decode($json, true);
        $this->assertArrayHasKey('node', $decoded['service']);
        $this->assertArrayHasKey('name', $decoded['service']['node']);
        $this->assertEquals($expected, $decoded['service']['node']['name']);
    }
}
