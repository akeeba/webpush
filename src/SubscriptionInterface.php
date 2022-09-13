<?php

declare(strict_types=1);

namespace Akeeba\WebPush;

/**
 * This class is a derivative work based on the WebPush library by Louis Lagrange. It has been modified to only use
 * dependencies shipped with Joomla itself and must not be confused with the original work.
 *
 * You can find the original code at https://github.com/web-push-libs
 *
 * The original code came with the following copyright notice:
 *
 * =====================================================================================================================
 *
 * This file is part of the WebPush library.
 *
 * (c) Louis Lagrange <lagrange.louis@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE-LAGRANGE.txt
 * file that was distributed with this source code.
 *
 * =====================================================================================================================
 *
 * @author Sergii Bondarenko <sb@firstvector.org>
 */
interface SubscriptionInterface
{
	public function getAuthToken(): ?string;

	public function getContentEncoding(): ?string;

	public function getEndpoint(): string;

	public function getPublicKey(): ?string;
}
