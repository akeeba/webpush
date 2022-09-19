<?php
/**
 * Akeeba WebPush
 *
 * An abstraction layer for easier implementation of WebPush in Joomla components.
 *
 * @copyright (c) 2022 Akeeba Ltd
 * @license   GNU GPL v3 or later; see LICENSE.txt
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Akeeba\WebPush;

use Akeeba\WebPush\WebPush\VAPID;
use Exception;
use Joomla\Application\ApplicationInterface;
use Joomla\CMS\Cache\CacheControllerFactoryInterface;
use Joomla\CMS\Cache\Controller\CallbackController;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use RuntimeException;
use Throwable;

/**
 * Trait for models implementing Web Push
 *
 * @since  1.0.0
 */
trait WebPushModelTrait
{
	private static $vapidKeys = [];

	/**
	 * Return the VAPID keys for this component
	 *
	 * @param   string  $option     The component's name
	 * @param   string  $configKey  The component configuration key holding the VAPID key pair
	 *
	 * @return  array{publicKey: string, privateKey: string}
	 * @since   1.0.0
	 */
	public function getVapidKeys(string $option, string $configKey = 'vapidKey'): array
	{
		if (is_array(self::$vapidKeys[$option] ?? null))
		{
			return self::$vapidKeys[$option];
		}

		$json = ComponentHelper::getParams($option)->get($configKey);

		if (!empty($json))
		{
			try
			{
				self::$vapidKeys[$option] = @json_decode($json, true);
			}
			catch (Exception $e)
			{
				self::$vapidKeys[$option] = null;
			}
		}

		if (is_array(self::$vapidKeys[$option]) && isset(self::$vapidKeys[$option]['publicKey']) && isset(self::$vapidKeys[$option]['privateKey']))
		{
			return self::$vapidKeys[$option];
		}

		self::$vapidKeys[$option] = $this->getNewVapidKeys($option, $configKey);

		return self::$vapidKeys[$option];
	}

	/**
	 * Returns the user's Web Push subscription object, or NULL if it's not defined or invalid.
	 *
	 * @param   string    $option   The component's name
	 * @param   int|null  $user_id  The user ID to get the subscription for. NULL for current user.
	 *
	 * @return  object|null  The Web Push subscription object. NULL if not defined or invalid.
	 * @throws  Exception
	 * @since   1.0.0
	 */
	public function getWebPushSubscription(string $option, ?int $user_id = null): ?object
	{
		if (empty($user_id))
		{
			$app     = Factory::getApplication();
			$user_id = $app->getIdentity()->id;
		}

		$key = $option . '.webPushSubscription';

		/** @var DatabaseInterface $db */
		$db    = method_exists($this, 'getDatabase') ? $this->getDatabase() : $this->getDbo();
		$query = $db->getQuery(true)
		            ->select($db->quoteName('profile_value'))
		            ->from($db->quoteName('#__user_profiles'))
		            ->where([
			            $db->quoteName('user_id') . ' = :user_id',
			            $db->quoteName('profile_key') . ' = :key',
		            ])
		            ->bind(':user_id', $user_id, ParameterType::INTEGER)
		            ->bind(':key', $key, ParameterType::STRING);

		$json = $db->setQuery($query)->loadResult() ?: null;

		if (empty($json))
		{
			return null;
		}

		try
		{
			$object = @json_decode($json) ?: null;

			if (!is_object($object))
			{
				return null;
			}

			return $object;
		}
		catch (Exception $e)
		{
			return null;
		}
	}

	/**
	 * Save the Web Push user subscription record sent from the browser
	 *
	 * @param   string  $option  The component's name
	 * @param   string  $json    The JSON serialised Web Push registration sent by the browser
	 *
	 * @return  void
	 * @throws  Exception
	 * @since   1.0.0
	 */
	public function webPushSaveSubscription(string $option, string $json): void
	{
		// Try to decode the JSON we retrieved from the browser
		try
		{
			$subscriptionData = @json_decode($json);
		}
		catch (Exception $e)
		{
			$subscriptionData = null;
		}

		// Validate the format of the data we received from the browser
		if (
			!is_object($subscriptionData)
			|| !isset($subscriptionData->endpoint)
			|| !isset($subscriptionData->keys)
			|| !is_object($subscriptionData->keys)
			|| !isset($subscriptionData->keys->p256dh)
			|| !is_string($subscriptionData->keys->p256dh)
			|| empty($subscriptionData->keys->p256dh)
			|| !isset($subscriptionData->keys->auth)
			|| !is_string($subscriptionData->keys->auth)
			|| empty($subscriptionData->keys->auth)
		)
		{
			throw new RuntimeException('Invalid Web Push user subscription record');
		}

		// Get the user options key and the user ID
		$user    = Factory::getApplication()->getIdentity();
		$user_id = $user->id;
		$key     = $option . '.webPushSubscription';

		// Remove any existing options
		/** @var DatabaseInterface $db */
		$db    = method_exists($this, 'getDatabase') ? $this->getDatabase() : $this->getDbo();
		$query = $db->getQuery(true)
		            ->delete($db->quoteName('#__user_profiles'))
		            ->where([
			            $db->quoteName('user_id') . ' = :user_id',
			            $db->quoteName('profile_key') . ' = :key',
		            ])
		            ->bind(':user_id', $user_id, ParameterType::INTEGER)
		            ->bind(':key', $key, ParameterType::STRING);

		$db->setQuery($query)->execute();

		// Add the new options
		$profileObject = (object) [
			'user_id'       => $user_id,
			'profile_key'   => $key,
			'profile_value' => $json,
			'ordering'      => 0,
		];
		$db->insertObject('#__user_profiles', $profileObject);
	}

	/**
	 * Clear a cache group.
	 *
	 * Used internally when saving the component's options after creating new VAPID keys.
	 *
	 * @param   string                $group      The cache to clean, e.g. com_content
	 * @param   int                   $client_id  The application ID for which the cache will be cleaned
	 * @param   ApplicationInterface  $app        The current CMS application.
	 *
	 * @return  array Cache controller options, including cleaning result
	 * @throws  Exception
	 * @since   1.0.0
	 */
	private function clearCacheGroup(string $group, int $client_id, ApplicationInterface $app): array
	{
		// Get the default cache folder. Start by using the JPATH_CACHE constant.
		$cacheBaseDefault = JPATH_CACHE;
		$appClientId      = 0;

		if (method_exists($app, 'getClientId'))
		{
			$appClientId = $app->getClientId();
		}

		// -- If we are asked to clean cache on the other side of the application we need to find a new cache base
		if ($client_id != $appClientId)
		{
			$cacheBaseDefault = (($client_id) ? JPATH_SITE : JPATH_ADMINISTRATOR) . '/cache';
		}

		// Get the cache controller's options
		$options = [
			'defaultgroup' => $group,
			'cachebase'    => $app->get('cache_path', $cacheBaseDefault),
			'result'       => true,
		];

		try
		{
			$container = Factory::getContainer();

			if (empty($container))
			{
				throw new RuntimeException('Cannot get Joomla 4 application container');
			}

			/** @var CacheControllerFactoryInterface $cacheControllerFactory */
			$cacheControllerFactory = $container->get('cache.controller.factory');

			if (empty($cacheControllerFactory))
			{
				throw new RuntimeException('Cannot get Joomla 4 cache controller factory');
			}

			/** @var CallbackController $cache */
			$cache = $cacheControllerFactory->createCacheController('callback', $options);

			if (empty($cache) || !property_exists($cache, 'cache') || !method_exists($cache->cache, 'clean'))
			{
				throw new RuntimeException('Cannot get Joomla 4 cache controller');
			}

			$cache->cache->clean();
		}
		catch (Throwable $e)
		{
			$options['result'] = false;
		}

		return $options;
	}

	/**
	 * Create, save and return new VAPID keys.
	 *
	 * DO NOT RUN MORE THAN ONCE. Doing so will invalidate all Web Push registrations for existing users!
	 *
	 * @param   string  $option     The component's name
	 * @param   string  $configKey  The component configuration key holding the VAPID key pair
	 *
	 * @return  array{publicKey: string, privateKey: string}
	 * @throws  \ErrorException
	 * @since   1.0.0
	 */
	private function getNewVapidKeys(string $option, string $configKey = 'vapidKey'): array
	{
		$vapidKeys = VAPID::createVapidKeys();
		$params    = ComponentHelper::getParams($option);

		$params->set($configKey, json_encode($vapidKeys));

		/** @var DatabaseInterface $db */
		$db   = method_exists($this, 'getDatabase') ? $this->getDatabase() : $this->getDbo();
		$data = $params->toString('JSON');
		$sql  = $db->getQuery(true)
		           ->update($db->qn('#__extensions'))
		           ->set($db->qn('params') . ' = ' . $db->q($data))
		           ->where($db->qn('element') . ' = :option')
		           ->where($db->qn('type') . ' = ' . $db->q('component'))
		           ->bind(':option', $option);

		$db->setQuery($sql);

		try
		{
			$db->execute();

			// The component parameters are cached. We just changed them. Therefore we MUST reset the system cache which holds them.
			$app = Factory::getApplication();
			$this->clearCacheGroup('_system', 0, $app);
			$this->clearCacheGroup('_system', 1, $app);
		}
		catch (Exception $e)
		{
			// Don't sweat if it fails
		}

		// Reset ComponentHelper's cache
		$refClass = new \ReflectionClass(ComponentHelper::class);
		$refProp  = $refClass->getProperty('components');
		$refProp->setAccessible(true);
		$components                  = $refProp->getValue();
		$components[$option]->params = $params;
		$refProp->setValue($components);

		return $vapidKeys;
	}
}