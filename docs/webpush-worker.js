/*
 * Akeeba WebPush
 *
 * An abstraction layer for easier implementation of WebPush in Joomla components.
 *
 * @copyright (c) 2022-2023 Akeeba Ltd
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

self.addEventListener('push', (event) => {
	/**
	 * Extract the payload as JSON.
	 *
	 * The JSON document is an object with two keys, `title` and `options`.
	 *
	 * `title` is a string which contains the title of the push notification.
	 *
	 * `options` is a representation of the options passed to the ServiceWorkerRegistration.showNotification()
	 * JavaScript method.
	 *
	 * @see https://developer.mozilla.org/en-US/docs/Web/API/PushMessageData
	 * @see https://developer.mozilla.org/en-US/docs/Web/API/ServiceWorkerRegistration/showNotification
	 */
	const payload = event.data ? event.data.json() : {};

	// Keep the service worker alive until the notification is created.
	event.waitUntil(
		self.registration.showNotification(payload.title, payload.options ?? {})
	);
});