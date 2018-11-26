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

require_once __DIR__ . '/../Thrift/Agent.php';
require_once __DIR__ . '/../Thrift/Types.php';

use Jaeger\Thrift\Agent\AgentClient;
use Jaeger\Thrift\Agent\AgentIf;
use Jaeger\Thrift\Batch;

use Thrift\Protocol\TCompactProtocol;
use Thrift\Transport\TMemoryBuffer;

/**
 * This implementation of AgentIf uses a memory buffer to collect the raw
 * Thrift serialized data to send over UDP because Thrift doesn't provide a
 * UDP protocol implementation.
 */
class UDPClient implements AgentIf
{
    /**
     * @var string IP Address of the Jaeger host
     */
    private $host;

    /**
     * @var int UDP port of the Jaeger host that uses Thrift over compact
     *      protocol
     */
    private $port;

    /**
     * Create a UDP Client
     *
     * @param string $host IP Address of the Jaeger host
     * @param int $port UDP port of the Jaeger host that uses Thrift over
     *        compact protocol
     */
    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     * Send a Zipkin formatted batch. Note that this is not implemented or used.
     */
    public function emitZipkinBatch(array $spans)
    {
        // not implemented or used
        return false;
    }

    /**
     * Send a batch of spans to the Jaeger backend.
     *
     * @param Batch $batch The batch request
     */
    public function emitBatch(Batch $batch)
    {
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($socket === null) {
            return;
        }

        // Thrift doesn't provide a UDP protocol, so write into a buffer and
        // then manually send the data over UDP via a socket.
        $buffer = new TMemoryBuffer();
        $protocol = new TCompactProtocol($buffer);
        $client = new AgentClient(null, $protocol);

        $client->emitBatch($batch);

        try {
            while ($buffer->available()) {
                $data = $buffer->read(65507); // max size of DGRAM payload https://stackoverflow.com/a/38742429
                socket_sendto(
                    $socket,
                    $data,
                    strlen($data),
                    ( $buffer->available() ) ? MSG_EOR : MSG_EOF,
                    $this->host,
                    $this->port
                );
            }
        } finally {
            socket_close($socket);
        }
    }
}
