<?php

declare(strict_types=1);

namespace Akeeba\WebPush\ECC;

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
 * *********************************************************************
 * Copyright (C) 2012 Matyas Danter.
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES
 * OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
 * ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 * ***********************************************************************
 *
 * @internal
 */
class PublicKey
{
    /**
     * @var Point
     */
    private $point;

    public function __construct(Point $point)
    {
        $this->point = $point;
    }

    public function getPoint(): Point
    {
        return $this->point;
    }
}
