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
     * Convert an OpenCensus Span to its Jaeger Thrift representation.
     *
     * @access private
     *
     * @param SpanData $span The span to convert.
     * @return Span The Jaeger Thrift Span representation.
     */
    public static function convertSpan(SpanData $span)
    {
        $startTime = self::convertTimestamp($span->startTime());
        $endTime = self::convertTimestamp($span->endTime());
        $spanId = hexdec($span->spanId());
        $parentSpanId = hexdec($span->parentSpanId());
        list($highTraceId, $lowTraceId) = self::convertTraceId($span->traceId());

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
            'tags' => self::convertTags($span->attributes()),
            'logs' => self::convertLogs($span->timeEvents())
        ]);
    }

    /**
     * Convert an associative array of $key => $value to Jaeger Tags.
     */
    public static function convertTags(array $attributes)
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

    private static function convertLogs(array $timeEvents)
    {
        return array_map(function (TimeEvent $timeEvent) {
            if ($timeEvent instanceof Annotation) {
                return self::convertAnnotation($timeEvent);
            } elseif ($timeEvent instanceof MessageEvent) {
                return self::convertMessageEvent($timeEvent);
            } else {
            }
        }, $timeEvents);
    }

    private static function convertAnnotation(Annotation $annotation)
    {
        return new Log([
            'timestamp' => self::convertTimestamp($annotation->time()),
            'fields' => self::convertTags($annotation->attributes() + [
                'description' => $annotation->description()
            ])
        ]);
    }

    private static function convertMessageEvent(MessageEvent $messageEvent)
    {
        return new Log([
            'timestamp' => self::convertTimestamp($messageEvent->time()),
            'fields' => self::convertTags([
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
    private static function convertTimestamp(\DateTimeInterface $dateTime)
    {
        return (int)((float) $dateTime->format('U.u') * 1000 * 1000);
    }

    /**
     * Split the provided hexId into 2 64-bit integers (16 hex chars each).
     * Returns array of 2 int values.
     */
    private static function convertTraceId($hexId)
    {
        return array_slice(
            array_map(
                'hexdec',
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
