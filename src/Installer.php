<?php

namespace OpenCensus\Trace\Exporter;

class Installer
{
    public static function checkPhpExtDependency()
    {
        if (!extension_loaded('bcmath') && !extension_loaded('gmp')) {
            throw new \Exception('`opencensus-php-exporter-jaeger` requires one of the two extensions to be '
                . 'installed: `php-bcmath` or `php-gmp`');
        }
    }
}
