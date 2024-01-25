<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

// Licensed to Elasticsearch B.V under one or more agreements.
// Elasticsearch B.V licenses this file to you under the Apache 2.0 License.
// See the LICENSE file in the project root for more information

namespace Elastic\Tests\Monolog\Formatter;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class MockHandler extends AbstractProcessingHandler
{
    /** @var array<LogRecord> */
    public array $records = [];

    protected function write(LogRecord $record): void
    {
        $this->records[] = $record;
    }
}
