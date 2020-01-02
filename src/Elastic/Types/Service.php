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
 * @see https://www.elastic.co/guide/en/ecs/current/ecs-service.html
 *
 * @author Philip Krauss <philip.krauss@elastic.co>
 */
class Service extends BaseType implements JsonSerializable
{

    /**
     * @var array
     */
    private $data;

    /**
     * Unique identifier of the running service.
     *
     * <em>If the service is comprised of many nodes, the service.id should be the same for all nodes.</em>
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
     * Ephemeral identifier of this service (if one exists)
     *
     * <em>This id normally changes across restarts, but service.id does not.</em>
     *
     * @param mixed: string | int
     */
    final public function setEphemeralId(string $ephemeralId)
    {
        $this->data['ephemeral_id'] = $ephemeralId;
    }

    /**
     * Name of the service data is collected from
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
     * Name of a service node
     *
     * <em>
     * This allows for two nodes of the same service running on the same host to be differentiated.
     * Therefore, service.node.name should typically be unique across nodes of a given service.
     * </em>
     *
     * @param string
     */
    final public function setNodeName(string $nodeName)
    {
        $this->data['node'] = ['name' => $nodeName];
    }

    /**
     * Current state of the service
     *
     * @internal core
     *
     * @param string
     */
    final public function setState(string $state)
    {
        $this->data['state'] = $state;
    }

    /**
     * The type of the service data is collected from
     *
     * <em>The type can be used to group and correlate logs and metrics from one service type.</em>
     *
     * @internal core
     *
     * @param string
     */
    final public function setType(string $type)
    {
        $this->data['type'] = $type;
    }

    /**
     * Version of the service the data was collected from
     *
     * <em>This allows to look at a data set only for a specific version of a service.</em>
     *
     * @internal core
     *
     * @param string
     */
    final public function setVersion(string $version)
    {
        $this->data['version'] = $version;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return ['service' => $this->data];
    }
}
