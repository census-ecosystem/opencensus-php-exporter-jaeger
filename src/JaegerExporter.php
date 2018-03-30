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

namespace OpenCensus\Trace\Exporter;

require_once 'Thrift/Agent.php';
require_once 'Thrift/Types.php';

use OpenCensus\Trace\Exporter\Jaeger\SpanConverter;

use Jaeger\Thrift\Agent\AgentIf;
use Jaeger\Thrift\Agent\AgentClient;
use Jaeger\Thrift\Batch;
use Jaeger\Thrift\Process;

use Thrift\Protocol\TCompactProtocol;
use Thrift\Transport\TMemoryBuffer;

/**
 * This implementation of the ExporterInterface talks to a Jaeger Agent backend
 * using Thrift (Compact Protocol) over UDP.
 */
class JaegerExporter implements ExporterInterface
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var Process
     */
    protected $process;

    /**
     * @var AgentIf
     */
    protected $client;

    /**
     * Create a new Jaeger Exporter.
     *
     * @param string $serviceName Name of the traced process/service
     * @param array $options [optional] {
     *     @type string $host The ip address of the Jaeger service. **Defaults
     *           to** '127.0.0.1'.
     *     @type int $port The UDP port of the Jaeger service. **Defaults to*
     *           6831.
     *     @type array $tags Associative array of key => value
     * }
     */
    public function __construct($serviceName, array $options = [])
    {
        $options += [
            'host' => '127.0.0.1',
            'port' => 6831,
            'tags' => [],
            'client' => null
        ];
        $this->host = $options['host'];
        $this->port = (int) $options['port'];
        $this->process = new Process([
            'serviceName' => $serviceName,
            'tags' => SpanConverter::convertTags($options['tags'])
        ]);
        $this->client = $options['client'];
    }

    /**
     * Report the provided Trace to a backend.
     *
     * @param SpanData $spans
     * @return bool
     */
    public function export(array $spans)
    {
        if (empty($spans)) {
            return false;
        }

        $spans = array_map([SpanConverter::class, 'convertSpan'], $spans);

        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($socket === null) {
            return false;
        }

        // Thrift doesn't provide a UDP protocol, so write into a buffer and
        // then manually send the data over UDP via a socket.
        $buffer = new TMemoryBuffer();
        $protocol = new TCompactProtocol($buffer);
        $client = new AgentClient(null, $protocol);
        $batch = new Batch([
            'process' => $this->process,
            'spans' => $spans
        ]);

        $client->emitBatch($batch);
        $data = $buffer->getBuffer();

        try {
            $dataSize = strlen($data);
            return socket_sendto(
                $socket,
                $data,
                $dataSize,
                0,
                $this->host,
                $this->port
            ) === $dataSize;
        } finally {
            socket_close($socket);
        }
        return false;
    }
}
