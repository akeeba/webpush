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
 */
class Notification
{
	/** @var array Auth details : VAPID */
	private $auth;

	/** @var array Options : TTL, urgency, topic */
	private $options;

	/** @var null|string */
	private $payload;

	/** @var SubscriptionInterface */
	private $subscription;

	public function __construct(SubscriptionInterface $subscription, ?string $payload, array $options, array $auth)
	{
		$this->subscription = $subscription;
		$this->payload      = $payload;
		$this->options      = $options;
		$this->auth         = $auth;
	}

	public function getAuth(array $defaultAuth): array
	{
		return count($this->auth) > 0 ? $this->auth : $defaultAuth;
	}

	public function getOptions(array $defaultOptions = []): array
	{
		$options            = $this->options;
		$options['TTL']     = array_key_exists('TTL', $options) ? $options['TTL'] : $defaultOptions['TTL'];
		$options['urgency'] = array_key_exists('urgency', $options) ? $options['urgency'] : $defaultOptions['urgency'];
		$options['topic']   = array_key_exists('topic', $options) ? $options['topic'] : $defaultOptions['topic'];

		return $options;
	}

	public function getPayload(): ?string
	{
		return $this->payload;
	}

	public function getSubscription(): SubscriptionInterface
	{
		return $this->subscription;
	}
}
