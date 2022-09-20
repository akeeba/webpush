## Sending notifications

Sending notifications only requires having access to the `PushModel` and knowing the user's ID.

For example:

```php
// Get the model
$model = $this->getMVCFactory()->createModel('Push', 'Administrator');

// Set the notification title
$title = 'My notification title';

// Options are, well, optional! You can always have a notification consisting of just a title.
$options = new \Akeeba\WebPush\NotificationOptions();
$options->body = 'This is the notification body';
$options->icon = Uri::root() . 'media/com_example/images/example.png';

// Tell the model to send the notification to all devices and browsers subscribed by the user with user ID 123
$model->sendNotification($title, $options->toArray(), 123);
```
