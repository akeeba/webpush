<?php

declare(strict_types=1);

namespace Akeeba\WebPush\ECC;

use Brick\Math\BigInteger;

/**
 * This class is copied verbatim from the JWT Framework by Spomky Labs.
 *
 * You can find the original code at https://github.com/web-token/jwt-framework
 *
 * The original file has the following copyright notice:
 *
 * =====================================================================================================================
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2014-2020 Spomky-Labs
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE-SPOMKY.txt file for details.
 *
 * =====================================================================================================================
 *
 * @internal
 */
class ModularArithmetic
{
    public static function sub(BigInteger $minuend, BigInteger $subtrahend, BigInteger $modulus): BigInteger
    {
        return $minuend->minus($subtrahend)->mod($modulus);
    }

    public static function mul(BigInteger $multiplier, BigInteger $muliplicand, BigInteger $modulus): BigInteger
    {
        return $multiplier->multipliedBy($muliplicand)->mod($modulus);
    }

    public static function div(BigInteger $dividend, BigInteger $divisor, BigInteger $modulus): BigInteger
    {
        return self::mul($dividend, Math::inverseMod($divisor, $modulus), $modulus);
    }
}
