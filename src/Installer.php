<?php

namespace OpenCensus\Trace\Exporter;

class Installer {
    public static function checkPhpExtDependency() {
        if (!function_exists('bcadd') && !function_exists('gmp_mul')) {
            throw new \Exception('`opencensus-php-exporter-jaeger` requires one of the two extensions to be installed: `php-bcmath` or `php-gmp`');
        }
    }
}
