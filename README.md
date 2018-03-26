# OpenCensus Jaeger Exporter for PHP

This library provides an [`ExporterInterface`][exporter-interface] for exporting
Trace data to a Jaeger instance.

[![CircleCI](https://circleci.com/gh/census-instrumentation/opencensus-php-exporter-jaeger.svg?style=svg)](https://circleci.com/gh/census-instrumentation/opencensus-php-exporter-jaeger)
[![Packagist](https://img.shields.io/packagist/v/opencensus/opencensus-exporter-jaeger.svg)](https://packagist.org/packages/opencensus/opencensus)
![PHP-Version](https://img.shields.io/packagist/php-v/opencensus/opencensus-exporter-jaeger.svg)

## Installation & basic usage

1. Install the `opencensus/opencensus-exporter-jaeger` package using [composer][composer]:

    ```bash
    $ composer require opencensus/opencensus-exporter-jaeger:~0.1
    ```

1. Initialize a tracer for your application:

    ```php
    use OpenCensus\Trace\Tracer;
    use OpenCensus\Trace\Exporter\JaegerExporter;

    Tracer::start(new JaegerExporter('my-service-name'));
    ```

## Customization

TODO: Fill out these instructions

## Versioning

[![Packagist](https://img.shields.io/packagist/v/opencensus/opencensus-exporter-jaeger.svg)](https://packagist.org/packages/opencensus/opencensus-exporter-jaeger)

This library follows [Semantic Versioning][semver].

Please note it is currently under active development. Any release versioned
0.x.y is subject to backwards incompatible changes at any time.

**GA**: Libraries defined at a GA quality level are stable, and will not
introduce backwards-incompatible changes in any minor or patch releases. We will
address issues and requests with the highest priority.

**Beta**: Libraries defined at a Beta quality level are expected to be mostly
stable and we're working towards their release candidate. We will address issues
and requests with a higher priority.

**Alpha**: Libraries defined at an Alpha quality level are still a
work-in-progress and are more likely to get backwards-incompatible updates.

**Current Status:** Alpha


## Contributing

Contributions to this library are always welcome and highly encouraged.

See [CONTRIBUTING](CONTRIBUTING.md) for more information on how to get started.

## Releasing

See [RELEASING](RELEASING.md) for more information on releasing new versions.

## License

Apache 2.0 - See [LICENSE](LICENSE) for more information.

## Disclaimer

This is not an official Google product.

[census-org]: https://github.com/census-instrumentation
[api-docs]: https://census-instrumentation.github.io/opencensus-php/api
[integration-docs]: https://census-instrumentation.github.io/opencensus-php
[composer]: https://getcomposer.org/
[pecl]: https://pecl.php.net/
[never-sampler]: https://census-instrumentation.github.io/opencensus-php/api/OpenCensus/Trace/Sampler/NeverSampleSampler.html
[always-sampler]: https://census-instrumentation.github.io/opencensus-php/api/OpenCensus/Trace/Sampler/NeverSampleSampler.html
[qps-sampler]: https://census-instrumentation.github.io/opencensus-php/api/OpenCensus/Trace/Sampler/NeverSampleSampler.html
[probability-sampler]: https://census-instrumentation.github.io/opencensus-php/api/OpenCensus/Trace/Sampler/NeverSampleSampler.html
[echo-exporter]: https://census-instrumentation.github.io/opencensus-php/api/OpenCensus/Trace/Exporter/EchoExporter.html
[file-exporter]: https://census-instrumentation.github.io/opencensus-php/api/OpenCensus/Trace/Exporter/FileExporter.html
[stackdriver-exporter]: https://census-instrumentation.github.io/opencensus-php/api/OpenCensus/Trace/Exporter/StackdriverExporter.html
[logger-exporter]: https://census-instrumentation.github.io/opencensus-php/api/OpenCensus/Trace/Exporter/LoggerExporter.html
[null-exporter]: https://census-instrumentation.github.io/opencensus-php/api/OpenCensus/Trace/Exporter/NullExporter.html
[zipkin-exporter]: https://census-instrumentation.github.io/opencensus-php/api/OpenCensus/Trace/Exporter/ZipkinExporter.html
[semver]: http://semver.org/
