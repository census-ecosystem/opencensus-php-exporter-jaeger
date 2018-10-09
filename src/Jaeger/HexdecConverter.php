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

namespace OpenCensus\Trace\Exporter\Jaeger;

use OpenCensus\Trace\Exporter\Jaeger\HexdecConverterInterface;

 /**
  * Hexdec converter class. Used for converting hex string values into numbers by using
  * `gmp` as large numbers library.
  */
class HexdecConverter implements HexdecConverterInterface
{

    const MAX_INT_64S = '9223372036854775807';

    /**
     * Hexdec convertion method for large numbers with limitation to PhP's signed INT64, using gmp.
     * Warning: Method may not work with hex numbers larger than 8 'digits'.
     *
     * @param str $hex
     * @return number
     */
    public function convert($hex)
    {
        $dec = 0;
        $len = strlen($hex);
        for ($i = 1; $i <= $len; $i++) {
            $dec = gmp_add($dec, gmp_mul(strval(hexdec($hex[$i - 1])), gmp_pow('16', strval($len - $i))));
        }
        if (gmp_cmp($dec, self::MAX_INT_64S) > 0) {
            $dec = gmp_sub(gmp_and($dec, self::MAX_INT_64S), gmp_add(self::MAX_INT_64S, '1'));
        }
        return intval($dec);
    }
}
