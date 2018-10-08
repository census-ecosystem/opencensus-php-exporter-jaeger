<?php
/**
 * Copyright 2018 OpenCensus Authors
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenCensus\Tests\Unit\Trace\Exporter\Jaeger;

require_once __DIR__ . '/SpanConverterTest.php';

use OpenCensus\Tests\Unit\Trace\Exporter\Jaeger\SpanConverterTest;

use OpenCensus\Trace\Exporter\Jaeger\HexdecConverterBcMath;
use OpenCensus\Trace\Exporter\Jaeger\SpanConverter;

class SpanConverterBcMathTest extends SpanConverterTest
{
    public function setUp()
    {
        parent::setUp();
        $this->converter = new SpanConverter([
            'hexdecConverter' => new HexdecConverterBcMath()
        ]);
    }
}
