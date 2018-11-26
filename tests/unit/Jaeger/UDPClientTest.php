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

namespace OpenCensus\Trace\Exporter\Jaeger;

require_once 'src/Thrift/Types.php';

use Jaeger\Thrift\Batch;
use Jaeger\Thrift\Process;
use Jaeger\Thrift\Span;
use Thrift\Protocol\TCompactProtocol;
use Thrift\Transport\TMemoryBuffer;
use Prophecy\Argument;
use PHPUnit\Framework\TestCase;

function socket_create($domain, $type, $protocol)
{
    return UDPClientTest::$sockets->socket_create($domain, $type, $protocol);
}

function socket_sendto($socket, $buf, $len, $flags, $addr, $port)
{
    return UDPClientTest::$sockets->socket_sendto($socket, $buf, $len, $flags, $addr, $port);
}

function socket_close($socket)
{
    return UDPClientTest::$sockets->socket_close($socket);
}

interface SocketHandler
{
    public function socket_create($domain, $type, $protocol);

    public function socket_sendto($socket, $buf, $len, $flags, $addr, $port);

    public function socket_close($socket);
}

class UDPClientTest extends TestCase
{
    public static $sockets;

    public function setUp()
    {
        if (!extension_loaded('sockets')) {
            $this->markTestSkipped('UDPClient requires sockets extension');
        }
        parent::setUp();
        self::$sockets = null;
    }

    public function testUsesSockets()
    {
        $resource = new \stdClass();
        $batch = new Batch([
            'process' => new Process([
                'serviceName' => 'test-app'
            ]),
            'spans' => [
                new Span([
                    'traceIdHigh' => 1234,
                    'traceIdLow' => 5678,
                    'spanId' => 9012,
                    'parentSpanId' => 3456,
                    'operationName' => 'main',
                    'flags' => 0,
                    'startTime' => (int)(microtime(true) * 1000),
                    'duration' => 1234
                ])
            ]
        ]);

        $sockets = $this->prophesize(SocketHandler::class);
        $sockets->socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)
            ->willReturn($resource)
            ->shouldBeCalled();
        $sockets->socket_sendto(
            $resource,
            Argument::type('string'),
            Argument::type('int'),
            512,
            '1.1.1.1',
            1234
        )->willReturn(123)->shouldBeCalledTimes(1);
        $sockets->socket_close($resource)
            ->shouldBeCalled();
        self::$sockets = $sockets->reveal();

        $client = new UDPClient('1.1.1.1', 1234);
        $client->emitBatch($batch);
    }
}
