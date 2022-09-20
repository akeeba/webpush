## View and view template

The HTML view object needs to pass the options to the UI JavaScript and load said JavaScript.

For example, you can add this to your display() method:

```php
// Pass parameters to the JavaScript
$this->document->addScriptOptions('akeeba.webPush', [
    'workerUri'         => $this->document
        ->getWebAssetManager()
        ->getAsset('script', 'com_example.webpush-worker')
        ->getUri('true'),
    'subscribeUri'      => Route::_(
        'index.php?option=com_example&task=push.webpushsubscribe',
        false,
        Route::TLS_IGNORE,
        true
    ),
    'unsubscribeUri'    => Route::_(
        'index.php?option=com_example&task=push.webpushunsubscribe',
        false,
        Route::TLS_IGNORE,
        true
    ),
    'vapidKeys'         => $this->getModel()->getVapidKeys('com_example'),
    'subscribeButton'   => '#btnWebPushSubscribe',
    'unsubscribeButton' => '#btnWebPushUnsubscribe',
    'unavailableInfo'   => '#webPushNotAvailable',    
]);
// Load the JavaScript
$this->document->getWebAssetManager()->useScript('com_example.webpush');
```

The view template simply needs to provide the interface elements referenced by the options. For example:

```php
<div class="card">
	<h3 class="card-header bg-primary text-white">
		<?= Text::_('COM_EXAMPLE_WEBPUSH_HEAD') ?>
	</h3>
	<div class="card-body">
		<div class="alert alert-warning d-none" id="webPushNotAvailable">
			<h3 class="alert-heading"><?= Text::_('COM_EXAMPLE_WEBPUSH_LBL_UNAVAILABLE_HEAD') ?></h3>
			<p><?= Text::_('COM_EXAMPLE_WEBPUSH_LBL_UNAVAILABLE_BODY') ?></p>
		</div>
		<button
			type="button"
			id="btnWebPushSubscribe"
			class="btn btn-primary d-none disabled"
			>
			<?= Text::_('COM_EXAMPLE_WEBPUSH_BTN_SUBSCRIBE') ?>
		</button>
		<button
			type="button"
			id="btnWebPushUnsubscribe"
			class="btn btn-danger d-none disabled"
			>
			<?= Text::_('COM_EXAMPLE_WEBPUSH_BTN_UNSUBSCRIBE') ?>
		</button>
	</div>
</div>
```

To understand this better, here are the associated language strings:

```ini
COM_EXAMPLE_WEBPUSH_HEAD="Web Push Demo"
COM_EXAMPLE_WEBPUSH_BTN_SUBSCRIBE="Enable Push Notifications"
COM_EXAMPLE_WEBPUSH_BTN_UNSUBSCRIBE="Disable Push Notifications"
COM_EXAMPLE_WEBPUSH_LBL_UNAVAILABLE_HEAD="Push notifications not available"
COM_EXAMPLE_WEBPUSH_LBL_UNAVAILABLE_BODY="Your browser does not support Web Push. Web Push is required to send push notifications. Please use a recent version of a browser supporting Web Push such as Edge, Chrome, Opera, or Firefox."
```