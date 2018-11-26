# OpenCensus Jaeger Exporter for PHP

This library provides an [`ExporterInterface`][exporter-interface] for exporting
Trace data to a Jaeger instance using Thrift (Compact Protocol) over UDP.

[![CircleCI](https://circleci.com/gh/census-ecosystem/opencensus-php-exporter-jaeger.svg?style=svg)][ci-build]
[![Packagist](https://img.shields.io/packagist/v/opencensus/opencensus-exporter-jaeger.svg)][packagist-package]
![PHP-Version](https://img.shields.io/packagist/php-v/opencensus/opencensus-exporter-jaeger.svg)

## Requirements

* PHP >= 5.6
* 64-bit version of PHP

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

You may provide an associative array of options to the `JaegerExporter` at
initialization:

```php
$options = [];
$exporter = new JaegerExporter('my-service-name', $options);
```

The following options are available:

| Option | Default | Description |
| ------ | ------- | ----------- |
| `host` | "127.0.0.1" | The TCP IP address to send the UDP request to |
| `port` | 6831 | The TCP port to send the UDP request to |
| `tags` | (empty) | An associative array of tags to mark this process with |
| `client` | null | Optional [`AgentIf`][agent-interface] interface to use for testing |

## Versioning

[![Packagist](https://img.shields.io/packagist/v/opencensus/opencensus-exporter-jaeger.svg)][packagist-package]

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

[exporter-interface]: https://github.com/census-instrumentation/opencensus-php/blob/master/src/Trace/Exporter/ExporterInterface.php
[census-org]: https://github.com/census-instrumentation
[composer]: https://getcomposer.org/
[agent-interface]: https://github.com/census-instrumentation/opencensus-php-exporter-jaeger/blob/master/src/Thrift/Agent.php#L19
[semver]: http://semver.org/
[ci-build]: https://circleci.com/gh/census-ecosystem/opencensus-php-exporter-jaeger
[packagist-package]: https://packagist.org/packages/opencensus/opencensus-exporter-jaeger
