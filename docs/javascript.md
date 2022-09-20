## JavaScript

There are two files needed for WebPush to work. We have written two sample files to make it easier for you to understand what to do. You can use them verbatim, or modified, if you do not want to write your own JavaScript.

First, a bit of theory — or, rather, the 30,000 feet overview.

When a user subscribes their browser to Web Push notifications two things happen. 

First, the browser installs a small piece of JavaScript called a Service Worker and assigns it to handle the Web Push notifications. The Service Worker is ‘woken up’ whenever a push notification comes in. It is responsible for decoding it and decide what to do with it, including showing the actual notification through the JavaScript [Notifications API](https://developer.mozilla.org/en-US/docs/Web/API/Notifications_API).

Second, the browser talks to its manufacturer (e.g. Google Chrome talks to Google) and gets a special URL where push notifications are meant to be sent. The browser also creates a set of keys which are known only to the browser, but not its manufacturer. Our user interface JavaScript is responsible for sending this information to the server where it can be safely stored and used whenever we need to send a notification to the user.

Conversely, when a user is unsubscribed from push notifications, two similar things happen but in reverse order.

First, the browser gives us a copy of the push notification subscription information (special URL and keys) so we can tell our server to remove them.

Second, the browser talks to its manufacturer and asks it to remove the special URL. Finally, the browser removes our Service Worker and destroys the copies of the keys it had in memory.

Therefore, we need two pieces of JavaScript: a tiny Service Worker which is ultimately saved in the browser and a bigger user interface JavaScript which handles the dirty business of subscribing and unsubscribing the user from push notifications.

### Service Worker

You need a [Service Worker](webpush-worker.js) which is installed in the browser and is responsible for displaying the notifications received through Web Push.

The default implementation of the Service Worker is fairly simple and can only display minimal notifications. If you want to implement custom notification actions you will need to customise this JavaScript.

### User interface JavaScript

The [user interface JavaScript](webpush.js) handles the user subscription and unsubscription to push notifications. This is configured through parameters we send from the server through [our View class](view.md).

The default implementation takes the following parameters:
* `workerUri`. Required. The URL to our Service Worker JavaScript file.
* `subscribeUri`. Required. The URL which will handle a new Web Push subscription.
* `unsubscribeUri`. Optional. The URL which will handle a new Web Push unsubscription.
* `vapidKeys`. Required. An array with the VAPID keys (cryptographic keys) which ensure confidentiality of push notifications' contents.
* `subscribeButton`. Required. A CSS element query to the interface element which, when clicked, will initialise the subscription process to Web Push notifications.
* `unsubscribeButton`. Optional. A CSS element query to the interface element which, when clicked, will initialise the unsubscription process from Web Push notifications.
* `unavailableInfo`. Optional. A CSS element query which will be displayed if Web Push is not available on the user's browser.

If you do not provide the items related to unsubscription it will not be possible for the user to unsubscribe from Web Push notifications.

If you do not provide the `unavailableInfo` key there will be no interface elements visible if the browser does not support Web Push. This might be confusing to the users.

If you do not provide some required parameter the UI JavaScript will NOT work. You will receive errors in the browser's Console.

#### JavaScript Events

The default implementation sends two custom JavaScript events to the `window` object.

**`onAkeebaWebPushNotSubscribed`**

This event is raised when the user is not yet subscribed to push notifications. It is also raised after successful unsubscription from push notifications.

**`onAkeebaWebPushSubscribed`**

This event is raised when the user is already subscribed to push notifications. It is also raised after successful subscription to push notifications.

You can handle these events with very simply JavaScript like this:

```js
window.addEventListener('onAkeebaWebPushNotSubscribed', (e) => {
	// Do something is the user is not subscribed to push notifications
});

window.addEventListener('onAkeebaWebPushSubscribed', (e) => {
	// Do something is the user is subscribed to push notifications
});
```

For example, you can use these events to show/hide interface elements which are only applicable to push notifications.