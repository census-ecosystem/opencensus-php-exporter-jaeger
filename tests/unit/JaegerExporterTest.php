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

namespace OpenCensus\Tests\Unit\Trace\Exporter;

require_once 'src/Thrift/Agent.php';

use OpenCensus\Trace\Exporter\JaegerExporter;
use OpenCensus\Trace\Annotation;
use OpenCensus\Trace\MessageEvent;
use OpenCensus\Trace\Span as OCSpan;
use Prophecy\Argument;
use Jaeger\Thrift\Span;
use Jaeger\Thrift\Agent\AgentIf;
use PHPUnit\Framework\TestCase;

/**
 * @group trace
 */
class JaegerExporterTest extends TestCase
{
    private $client;

    public function setUp()
    {
        parent::setUp();
        $this->client = $this->prophesize(AgentIf::class);
    }

    public function testFormatsTrace()
    {
        $this->client->emitBatch(
            Argument::any()
        )->willReturn(null)->shouldBeCalled();
        $exporter = new JaegerExporter('test-agent', [
            'client' => $this->client->reveal()
        ]);
        $span = new OCSpan([
            'name' => 'span-name',
            'traceId' => 'aaa',
            'spanId' => 'bbb',
            'startTime' => new \DateTime(),
            'endTime' => new \DateTime()
        ]);
        $this->assertTrue($exporter->export([$span->spanData()]));
    }

    public function testEmptyTrace()
    {
        $exporter = new JaegerExporter('test-agent', [
            'client' => $this->client->reveal()
        ]);
        $this->assertFalse($exporter->export([]));
    }
}
