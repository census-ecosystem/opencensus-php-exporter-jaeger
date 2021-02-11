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
     * @var array
     */
    private $tags;

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
        $this->tags = $this->spanConverter->convertTags($options['tags']);
        $this->client = $options['client'];

        // if this option is passed, the spans with a particular prefix would be exported
        // with that serviceName.
        // eg. prefixServiceNameMap => ['PDO' => 'app_db', 'Predis' => 'app_redis'];

        if (array_key_exists('prefixServiceNameMap', $options)){
            $this->prefixServiceNameMap = $options['prefixServiceNameMap'];
        }
        else{
            $this->prefixServiceNameMap = [];
        }

        $this->prefixServiceNameMap += ['_default_' => $serviceName];
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

        // create different span buckets for each prefix
        $buckets = [];
        foreach (array_keys($this->prefixServiceNameMap) as $prefix){
            $buckets[$prefix] = [];
        }

        foreach ($spans as $s){
            $bucketed = false;
            foreach (array_keys($buckets) as $prefix){
                // if span name starts with a particular prefix, put the span in that bucket
                if (strpos($s->name(), $prefix) === 0){
                    $buckets[$prefix][] = $s;
                    $bucketed = true;
                    break;
                }
            }
            if (!$bucketed){
                $buckets['_default_'][] = $s;
            }
        }

        $client = $this->client ?: new UDPClient($this->host, $this->port);

        foreach ($buckets as $prefix => $spanBucket){
            if (count($spanBucket) != 0){
                $process = new Process([
                    'serviceName' => $this->prefixServiceNameMap[$prefix],
                    'tags' => $this->tags
                ]);

                $batch = new Batch([
                    'process' => $process,
                    'spans' => array_map([$this->spanConverter, 'convertSpan'], $spanBucket)
                ]);
                $client->emitBatch($batch);
            }
        }

        return true;
    }
}
