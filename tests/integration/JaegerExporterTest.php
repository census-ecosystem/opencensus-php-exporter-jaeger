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

namespace OpenCensus\Tests\Integration\Trace\Exporter;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

class JaegerExporterTest extends TestCase
{
    private static $jaegerClient;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $jaegerHost = getenv('JAEGER_HOST') ?: 'localhost';
        $jaegerPort = (int)(getenv('JAEGER_PORT') ?: 16686);
        self::$jaegerClient = new Client([
            'base_uri' => sprintf('http://%s:%d/', $jaegerHost, $jaegerPort)
        ]);
    }

    public function testReportsTraceToJaeger()
    {
        $rand = mt_rand();
        $client = new Client(['base_uri' => 'http://localhost:9000']);
        $response = $client->request('GET', '/', [
            'query' => [
                'rand' => $rand
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Hello world!', $response->getBody()->getContents());

        $response = $this->findTraces($rand);
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode($response->getBody()->getContents(), true);
        $this->assertCount(1, $data['data']);
        $trace = $data['data'][0];
        $this->assertCount(2, $trace['spans']);
    }

    public function testCanReachJaegerServer()
    {
        $response = self::$jaegerClient->request('GET', '/search');
        $this->assertEquals(200, $response->getStatusCode());
    }

    private function findTraces($rand)
    {
        return self::$jaegerClient->request('GET', '/api/traces', [
            'query' => [
                'service' => 'integration-test',
                'operation' => "/?rand=$rand",
                'limit' => 20,
                'lookback' => '1h'
            ]
        ]);
    }
}
