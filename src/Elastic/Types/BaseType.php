<?php declare(strict_types=1);

// Licensed to Elasticsearch B.V under one or more agreements.
// Elasticsearch B.V licenses this file to you under the Apache 2.0 License.
// See the LICENSE file in the project root for more information

namespace Elastic\Types;

class BaseType
{

    /**
     * Serialize self to JSON
     */
    public function __toString()
    {
        return json_encode($this);
    }
}
