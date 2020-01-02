<?php

declare(strict_types=1);

// Licensed to Elasticsearch B.V under one or more agreements.
// Elasticsearch B.V licenses this file to you under the Apache 2.0 License.
// See the LICENSE file in the project root for more information

namespace Elastic\Types;

use JsonSerializable;

/**
 * Serializes to ECS Tracing
 *
 * @version v1.x
 *
 * @see https://www.elastic.co/guide/en/ecs/current/ecs-tracing.html
 * @see Elastic\Types\TracingTest
 *
 * @author Philip Krauss <philip.krauss@elastic.co>
 */
class Tracing extends BaseType implements JsonSerializable
{

    /**
     * Unique identifier of the trace
     *
     * @var string
     */
    private $traceId;

    /**
     * Unique identifier of the transaction
     *
     * @var string | null
     */
    private $transactionId;

    /**
     * @param string $traceId
     * @param string $transactionId, Def: null
     */
    public function __construct(string $traceId, ?string $transactionId = null)
    {
        $this->traceId       = $traceId;
        $this->transactionId = $transactionId;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $message['trace'] = ['id' => $this->traceId];
        if ($this->transactionId !== null) {
            $message['transaction'] = ['id' => $this->transactionId];
        }
        return $message;
    }
}
