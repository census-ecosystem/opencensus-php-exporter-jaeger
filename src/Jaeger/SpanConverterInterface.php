<?php

/**
 * Copyright 2018 OpenCensus Authors
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenCensus\Trace\Exporter\Jaeger;

use OpenCensus\Trace\Annotation;
use OpenCensus\Trace\MessageEvent;
use OpenCensus\Trace\SpanData;
use OpenCensus\Trace\TimeEvent;

use Jaeger\Thrift\Log;
use Jaeger\Thrift\Span;
use Jaeger\Thrift\Tag;
use Jaeger\Thrift\TagType;

/**
 * This class handles converting from the OpenCensus data model into its
 * Jaeger Thrift representation.
 */
interface SpanConverterInterface
{
    /**
     * Convert an OpenCensus Span to its Jaeger Thrift representation.
     *
     * @access private
     *
     * @param SpanData $span The span to convert.
     * @return Span The Jaeger Thrift Span representation.
     */
    public function convertSpan(SpanData $span);

    /**
     * Convert an associative array of $key => $value to Jaeger Tags.
     */
    public function convertTags(array $attributes);
}
