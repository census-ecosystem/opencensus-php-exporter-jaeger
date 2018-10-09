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

require_once __DIR__ . '/../Thrift/Types.php';

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
class SpanConverter
{

    /**
     *@var HexdecConverterInterface
     */
    private $hexdec = null;

    /**
     * Create a new Span Converter.
     *
     * @param array $options [optional] {
     *     @type HexdecConvertor convertor for hex values
     * }
     */
    public function __construct(array $options = [])
    {
        $this->hexdec = empty($options['hexdecConverter']) ? new HexdecConverter() : $options['hexdecConverter'];
    }

    /**
     * Convert an OpenCensus Span to its Jaeger Thrift representation.
     *
     * @param SpanData $span The span to convert.
     * @return Span The Jaeger Thrift Span representation.
     */
    public function convertSpan(SpanData $span)
    {
        $startTime = $this->convertTimestamp($span->startTime());
        $endTime = $this->convertTimestamp($span->endTime());
        $spanId = $this->hexdec->convert($span->spanId());
        $parentSpanId = $this->hexdec->convert($span->parentSpanId());
        list($highTraceId, $lowTraceId) = $this->convertTraceId($span->traceId());

        return new Span([
            'traceIdLow' => $lowTraceId,
            'traceIdHigh' => $highTraceId,
            'spanId' => $spanId,
            'parentSpanId' => $parentSpanId,
            'operationName' => $span->name(),
            'references' => [], // for now, links cannot describe references
            'flags' => 0,
            'startTime' => $startTime,
            'duration' => $endTime - $startTime,
            'tags' => $this->convertTags($span->attributes()),
            'logs' => $this->convertLogs($span->timeEvents())
        ]);
    }

    /**
     * Convert an associative array of $key => $value to Jaeger Tags.
     */
    public function convertTags(array $attributes)
    {
        $tags = [];
        foreach ($attributes as $key => $value) {
            $tags[] = new Tag([
                'key' => (string) $key,
                'vType' => TagType::STRING,
                'vStr' => (string) $value
            ]);
        }
        return $tags;
    }

    /**
     *
     * @param array $timeEvents
     * @return array
     */
    private function convertLogs(array $timeEvents)
    {
        return array_map(function (TimeEvent $timeEvent) {
            if ($timeEvent instanceof Annotation) {
                return $this->convertAnnotation($timeEvent);
            } elseif ($timeEvent instanceof MessageEvent) {
                return $this->convertMessageEvent($timeEvent);
            } else {
            }
        }, $timeEvents);
    }

    /**
     *
     * @param Annotation $annotation
     * @return Log
     */
    private function convertAnnotation(Annotation $annotation)
    {
        return new Log([
            'timestamp' => $this->convertTimestamp($annotation->time()),
            'fields' => $this->convertTags($annotation->attributes() + [
                'description' => $annotation->description()
            ])
        ]);
    }

    /**
     *
     * @param MessageEvent $messageEvent
     * @return Log
     */
    private function convertMessageEvent(MessageEvent $messageEvent)
    {
        return new Log([
            'timestamp' => $this->convertTimestamp($messageEvent->time()),
            'fields' => $this->convertTags([
                'type' => $messageEvent->type(),
                'id' => $messageEvent->id(),
                'uncompressedSize' => $messageEvent->uncompressedSize(),
                'compressedSize' => $messageEvent->compressedSize()
            ])
        ]);
    }

    /**
     * Return the given timestamp as an int in milliseconds.
     */
    private function convertTimestamp(\DateTimeInterface $dateTime)
    {
        return (int)((float) $dateTime->format('U.u') * 1000 * 1000);
    }

    /**
     * Split the provided hexId into 2 64-bit integers (16 hex chars each).
     * Returns array of 2 int values.
     *
     * @param str $hexId
     * @return array
     */
    private function convertTraceId($hexId)
    {
        return array_slice(
            array_map(
                [$this->hexdec, 'convert'],
                str_split(
                    substr(
                        str_pad($hexId, 32, "0", STR_PAD_LEFT),
                        -32
                    ),
                    16
                )
            ),
            0,
            2
        );
    }
}
