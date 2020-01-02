<?php

declare(strict_types=1);

// Licensed to Elasticsearch B.V under one or more agreements.
// Elasticsearch B.V licenses this file to you under the Apache 2.0 License.
// See the LICENSE file in the project root for more information

namespace Elastic\Types;

use JsonSerializable;
use Throwable;

/**
 * Serializes to ECS Error
 *
 * @version v1.x
 *
 * @see https://www.elastic.co/guide/en/ecs/current/ecs-error.html
 *
 * @author Philip Krauss <philip.krauss@elastic.co>
 */
class Error extends BaseType implements JsonSerializable
{

    /**
     * @var array
     */
    private $data;

    /**
     * @param Throwable $throwable
     */
    public function __construct(Throwable $throwable)
    {
        $this->data = [
            'error'   => [
                'type'        => get_class($throwable),
                'message'     => $throwable->getMessage(),
                'code'        => $throwable->getCode(),
                'stack_trace' => explode(PHP_EOL, $throwable->getTraceAsString()),
            ],
            'log'     => [
                'origin' => [
                    'file' => [
                        'name' => $throwable->getFile(),
                        'line' => $throwable->getLine(),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->data;
    }
}
