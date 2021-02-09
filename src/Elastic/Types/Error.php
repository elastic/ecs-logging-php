<?php

/** @noinspection PhpUndefinedClassInspection */

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
 * @see     https://www.elastic.co/guide/en/ecs/current/ecs-error.html
 *
 * @author  Philip Krauss <philip.krauss@elastic.co>
 */
class Error extends BaseType implements JsonSerializable
{
    /** @var Throwable */
    private $throwable;

    /**
     * @param Throwable $throwable
     */
    public function __construct(Throwable $throwable)
    {
        $this->throwable = $throwable;
    }

    public static function serialize(Throwable $throwable): array
    {
        return [
            'type'        => get_class($throwable),
            'message'     => $throwable->getMessage(),
            'code'        => $throwable->getCode(),
            'stack_trace' => $throwable->__toString(),
        ];
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return self::serialize($this->throwable);
    }
}
