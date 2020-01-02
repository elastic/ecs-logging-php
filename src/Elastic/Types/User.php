<?php

declare(strict_types=1);

// Licensed to Elasticsearch B.V under one or more agreements.
// Elasticsearch B.V licenses this file to you under the Apache 2.0 License.
// See the LICENSE file in the project root for more information

namespace Elastic\Types;

use JsonSerializable;

/**
 * Serializes to ECS Trace
 *
 * @version v1.x
 *
 * @see https://www.elastic.co/guide/en/ecs/current/ecs-user.html
 *
 * @author Philip Krauss <philip.krauss@elastic.co>
 */
class User extends BaseType implements JsonSerializable
{

    /**
     * @var array
     */
    private $data;

    /**
     * One or multiple unique identifiers of the user
     *
     * @internal core
     *
     * @param mixed: string | int
     */
    final public function setId($id)
    {
        $this->data['id'] = $id;
    }

    /**
     * Short name or login of the user
     *
     * @internal core
     *
     * @param string
     */
    final public function setName(string $name)
    {
        $this->data['name'] = $name;
    }

    /**
     * Name of the directory the user is a member of
     *
     * @param string
     */
    final public function setDomain(string $domain)
    {
        $this->data['domain'] = $domain;
    }

    /**
     * User's email address
     *
     * @param string
     */
    final public function setEmail(string $email)
    {
        $this->data['email'] = $email;
    }

    /**
     * User’s full name, if available
     *
     * @param string
     */
    final public function setFullName(string $fullName)
    {
        $this->data['full_name'] = $fullName;
    }

    /**
     * Unique user hash to correlate information for a user in anonymized form
     *
     * <em>Useful if user.id or user.name contain confidential information and cannot be used.</em>
     *
     * @param string
     */
    final public function setHash(string $hash)
    {
        $this->data['hash'] = $hash;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return ['user' => $this->data];
    }
}
