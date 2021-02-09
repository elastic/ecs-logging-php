<?php

declare(strict_types=1);

// Licensed to Elasticsearch B.V under one or more agreements.
// Elasticsearch B.V licenses this file to you under the Apache 2.0 License.
// See the LICENSE file in the project root for more information

namespace Elastic\Tests;

use Monolog\Logger;

class HelperForMonolog
{
    public static function logEmergency(Logger $logger, string $message, array &$logOrigin): void
    {
        $logOrigin['class'] = __CLASS__;
        $logOrigin['function'] = __FUNCTION__;
        $logOrigin['file'] = __FILE__;
        $logOrigin['line'] = __LINE__ + 1;
        $logger->emergency($message);
    }
}
