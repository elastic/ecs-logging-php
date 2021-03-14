<?php

declare(strict_types=1);

// Licensed to Elasticsearch B.V under one or more agreements.
// Elasticsearch B.V licenses this file to you under the Apache 2.0 License.
// See the LICENSE file in the project root for more information

namespace Elastic\Tests\Types;

use \Elastic\Tests\BaseTestCase;
use Elastic\Types\User;
use Elastic\Types\BaseType;

/**
 * Test: User (Type)
 *
 * @version v1.x
 *
 * @see Elastic\Types\User
 *
 * @author Philip Krauss <philip.krauss@elastic.co>
 */
class UserTest extends BaseTestCase
{

    /**
     * @covers Elastic\Types\User::__construct
     * @covers Elastic\Types\User::jsonSerialize
     * @covers Elastic\Types\User::setId
     * @covers Elastic\Types\User::setName
     */
    public function testSerialization()
    {
        $expected = [
            'id' => rand(1, 99999),
            'name' => 'foobar',
        ];
        $user = new User();
        $this->assertInstanceOf(BaseType::class, $user);

        $user->setId($expected['id']);
        $user->setName($expected['name']);

        $json = json_encode($user);

        // Comply to the ECS format
        $decoded = json_decode($json, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('user', $decoded);
        $this->assertArrayHasKey('id', $decoded['user']);
        $this->assertArrayHasKey('name', $decoded['user']);

        // Values correctly propagated
        $this->assertEquals($expected['id'], $decoded['user']['id']);
        $this->assertEquals($expected['name'], $decoded['user']['name']);
    }

    /**
     * @depends testSerialization
     *
     * @covers Elastic\Types\User::__construct
     * @covers Elastic\Types\User::jsonSerialize
     * @covers Elastic\Types\User::setDomain
     */
    public function testSeralizeDomain()
    {
        $expected = sprintf("example-%d.local", rand(10, 99));

        $user = new User();
        $user->setDomain($expected);

        $json = json_encode($user);

        $decoded = json_decode($json, true);
        $this->assertArrayHasKey('domain', $decoded['user']);
        $this->assertEquals($expected, $decoded['user']['domain']);
    }

    /**
     * @depends testSerialization
     *
     * @covers Elastic\Types\User::__construct
     * @covers Elastic\Types\User::jsonSerialize
     * @covers Elastic\Types\User::setEmail
     */
    public function testSeralizeEmail()
    {
        $expected = sprintf("fake-user-%d@elastic.co", rand(10, 99));

        $user = new User();
        $user->setEmail($expected);

        $json = json_encode($user);

        $decoded = json_decode($json, true);
        $this->assertArrayHasKey('email', $decoded['user']);
        $this->assertEquals($expected, $decoded['user']['email']);
    }

    /**
     * @depends testSerialization
     *
     * @covers Elastic\Types\User::__construct
     * @covers Elastic\Types\User::jsonSerialize
     * @covers Elastic\Types\User::setFullName
     */
    public function testSeralizeFullName()
    {
        $expected = sprintf("Max Mustermann the %dth", rand(4, 9));

        $user = new User();
        $user->setFullName($expected);

        $json = json_encode($user);

        $decoded = json_decode($json, true);
        $this->assertArrayHasKey('full_name', $decoded['user']);
        $this->assertEquals($expected, $decoded['user']['full_name']);
    }

    /**
     * @depends testSerialization
     *
     * @covers Elastic\Types\User::__construct
     * @covers Elastic\Types\User::jsonSerialize
     * @covers Elastic\Types\User::setHash
     */
    public function testSeralizeHash()
    {
        $expected = md5(time() . rand(0, 9));

        $user = new User();
        $user->setHash($expected);

        $json = json_encode($user);

        $decoded = json_decode($json, true);
        $this->assertArrayHasKey('hash', $decoded['user']);
        $this->assertEquals($expected, $decoded['user']['hash']);
    }
}
