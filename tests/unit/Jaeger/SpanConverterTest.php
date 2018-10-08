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

use OpenCensus\Trace\Exporter\Jaeger\SpanConverter;
use OpenCensus\Trace\Annotation;
use OpenCensus\Trace\MessageEvent;
use OpenCensus\Trace\Span as OCSpan;
use Prophecy\Argument;
use Jaeger\Thrift\Span;
use Jaeger\Thrift\Agent\AgentIf;
use PHPUnit\Framework\TestCase;

class SpanConverterTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->converter = new SpanConverter();
    }

    public function testFormatsTrace()
    {
        $span = new OCSpan([
            'name' => 'span-name',
            'traceId' => 'aaa',
            'spanId' => 'bbb',
            'startTime' => new \DateTime(),
            'endTime' => new \DateTime()
        ]);
        $span = $this->converter->convertSpan($span->spanData());
        $this->assertInstanceOf(Span::class, $span);
        $this->assertInternalType('string', $span->operationName);
        $this->assertInternalType('int', $span->traceIdHigh);
        $this->assertInternalType('int', $span->traceIdLow);
        $this->assertInternalType('int', $span->spanId);
        $this->assertInternalType('int', $span->startTime);
        $this->assertInternalType('int', $span->duration);

        $this->assertEquals('span-name', $span->operationName);
        $this->assertEquals(3003, $span->spanId);
    }

    public function testTimeEvents()
    {
        $span = new OCSpan([
            'traceId' => 'aaa',
            'timeEvents' => [
                new Annotation('some-description', [
                    'foo' => 'bar'
                ]),
                new MessageEvent(MessageEvent::TYPE_SENT, 'message-id', [
                    'uncompressedSize' => 234,
                    'compressedSize' => 123
                ])
            ],
            'startTime' => new \DateTime(),
            'endTime' => new \DateTime()
        ]);
        $span = $this->converter->convertSpan($span->spanData());
        $this->assertCount(2, $span->logs);
        $log1 = $span->logs[0];
        $this->assertInternalType('int', $log1->timestamp);
        $this->assertInternalType('int', $span->traceIdHigh);
        $this->assertInternalType('int', $span->traceIdLow);
        $this->assertCount(1, $log1->fields);
        $this->assertEquals('description', $log1->fields[0]->key);
        $this->assertEquals('some-description', $log1->fields[0]->vStr);

        $log2 = $span->logs[1];
        $this->assertInternalType('int', $log2->timestamp);
        $this->assertCount(4, $log2->fields);
        $this->assertEquals('type', $log2->fields[0]->key);
        $this->assertEquals('SENT', $log2->fields[0]->vStr);
        $this->assertEquals('id', $log2->fields[1]->key);
        $this->assertEquals('message-id', $log2->fields[1]->vStr);
        $this->assertEquals('uncompressedSize', $log2->fields[2]->key);
        $this->assertEquals('234', $log2->fields[2]->vStr);
        $this->assertEquals('compressedSize', $log2->fields[3]->key);
        $this->assertEquals('123', $log2->fields[3]->vStr);
    }

    public function testAttributes()
    {
        $span = new OCSpan([
            'attributes' => [
                'foo' => 'bar',
                'asdf' => 'qwer'
            ],
            'startTime' => new \DateTime(),
            'endTime' => new \DateTime()
        ]);
        $span = $this->converter->convertSpan($span->spanData());
        $this->assertCount(2, $span->tags);
        $this->assertEquals('foo', $span->tags[0]->key);
        $this->assertEquals('bar', $span->tags[0]->vStr);
        $this->assertEquals('asdf', $span->tags[1]->key);
        $this->assertEquals('qwer', $span->tags[1]->vStr);
    }

    /**
     * @dataProvider traceIdValues
     */
    public function testTraceId($traceId, $expectedHigh, $expectedLow)
    {
        $span = new OCSpan([
            'traceId' => $traceId,
            'startTime' => new \DateTime(),
            'endTime' => new \DateTime()
        ]);
        $spanData = $this->converter->convertSpan($span->spanData());
        $this->assertEquals($expectedHigh, $spanData->traceIdHigh);
        $this->assertEquals($expectedLow, $spanData->traceIdLow);
    }

    public function traceIdValues()
    {
        return [
            ['aaa', 0, 2730],
            ['aaa0000000000000bbb', 2730, 3003],
            ['10000000000000aaa0000000000000bbb', 2730, 3003],
            ['fd7a7112906349cc80bb3f6c6a385a85', -181708510409307700, -9170666481338787195],
            ['5d37220beb8d4310b3e906a94776b893', 6716874803838272272, -5482843747228665709],
        ];
    }
}
