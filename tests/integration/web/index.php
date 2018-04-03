<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use OpenCensus\Trace\Exporter\JaegerExporter;
use OpenCensus\Trace\Exporter\ZipkinExporter;
use OpenCensus\Trace\Tracer;

$host = getenv('JAEGER_HOST') ?: 'localhost';
$exporter = new JaegerExporter('integration-test', [
    'host' => $host,
    'tags' => [
        'asdf' => 'qwer'
    ]
]);

Tracer::start($exporter, [
    'attributes' => [
        'foo' => 'bar'
    ]
]);

Tracer::inSpan(
    ['name' => 'slow_function'],
    function () {
        usleep(50);
    }
);

echo 'Hello world!';
