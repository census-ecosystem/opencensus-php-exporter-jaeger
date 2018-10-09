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

require_once __DIR__ . '/Thrift/Agent.php';
require_once __DIR__ . '/Thrift/Types.php';

use OpenCensus\Trace\Exporter\Jaeger\SpanConverter;
use OpenCensus\Trace\Exporter\Jaeger\UDPClient;

use Jaeger\Thrift\Agent\AgentIf;
use Jaeger\Thrift\Batch;
use Jaeger\Thrift\Process;

/**
 * This implementation of the ExporterInterface talks to a Jaeger Agent backend
 * using Thrift (Compact Protocol) over UDP.
 */
class JaegerExporter implements ExporterInterface
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var Process
     */
    private $process;

    /**
     * @var AgentIf
     */
    private $client;

    /**
     * @var SpanConverter
     */
    private $spanConverter;

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
     *     @type AgentIf $client Agent interface for testing
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
        $this->spanConverter = empty($options['spanConverter']) ? new SpanConverter() : $options['spanConverter'];
        $this->process = new Process([
            'serviceName' => $serviceName,
            'tags' => $this->spanConverter->convertTags($options['tags'])
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

        $client = $this->client ?: new UDPClient($this->host, $this->port);
        $batch = new Batch([
            'process' => $this->process,
            'spans' => array_map([$this->spanConverter, 'convertSpan'], $spans)
        ]);

        $client->emitBatch($batch);
        return true;
    }
}
