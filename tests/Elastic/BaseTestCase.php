<?php

declare(strict_types=1);

// Licensed to Elasticsearch B.V under one or more agreements.
// Elasticsearch B.V licenses this file to you under the Apache 2.0 License.
// See the LICENSE file in the project root for more information

namespace Elastic\Tests;

use PHPUnit\Framework\TestCase;
use \InvalidArgumentException;
use \Throwable;

/**
 * Base Class for TestCases
 *
 * @author Philip Krauss <philip.krauss@elastic.co>
 */
class BaseTestCase extends TestCase
{

    /**
     * @return string
     */
    protected function generateTraceId(): string
    {
        return sprintf('4bf92f3577b34da6a3ce929d0e0e%s', rand(1000, 9999));
    }

    /**
     * @return string
     */
    protected function generateTransactionId(): string
    {
        return sprintf('00f067aa0ba90%s', rand(100, 999));
    }

    /**
     * @return InvalidArgumentException
     */
    protected static function generateException(): Throwable
    {
        return new InvalidArgumentException('This is a InvalidArgumentException', 42);
    }
}
