### The `config.xml` file

```xml
<?xml version="1.0" encoding="UTF-8"?>
<config>
    <inlinehelp button="show"/>

    <fieldset>
        <field
                name="vapidKey"
                type="hidden"
        />

        <!-- Your other component options go here… -->
    </fieldset>

    <fieldset
            name="permissions"
            label="JCONFIG_PERMISSIONS_LABEL"
            description="JCONFIG_PERMISSIONS_DESC"
    >

        <field
                name="rules"
                type="rules"
                label="JCONFIG_PERMISSIONS_LABEL"
                class="inputbox"
                filter="rules"
                component="com_example"
                section="component"/>
    </fieldset>
</config>
```

### The PushController
```php
namespace Acme\Example\Administrator\Controller;

defined('_JEXEC') || die;

use Akeeba\WebPush\WebPushControllerTrait;
use Joomla\CMS\MVC\Controller\BaseController;

class PushController extends BaseController
{
	use WebPushControllerTrait;

    /**
     * You only need to define this override if the model you are using has a different name than the Controller. 
     */
	public function getModel($name = 'Push', $prefix = 'Administrator', $config = [])
	{
		return parent::getModel($name, $prefix, $config);
	}

    /**
     * OPTIONAL. We send a sample notification when the user is subscribed to demonstrate that it worked. 
     * 
     * This is optional but good from a user experience point of view. Don't make the user wonder if their push 
     * notifications will work or what they will look like. Don't make your users think. 
     */
	protected function onAfterWebPushSaveSubscription(?object $subscription)
	{
		$this->getModel()->sendNotification(
			$this->app->get('sitename'),
			[
				'body' => 'Hello from com_example!'
			],
			null,
			$subscription
		);
	}
}
```

### The PushModel

```php
namespace Acme\Example\Administrator\Model;

defined('_JEXEC') || die;

use Akeeba\WebPush\WebPushModelTrait;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class PushModel extends BaseDatabaseModel
{
	use WebPushModelTrait;

	public function __construct($config = [], MVCFactoryInterface $factory = null)
	{
		parent::__construct($config, $factory);

        // REQUIRED. It tells the WebPushModelTrait how to find its configuration and your users' push subscriptions.
		$this->initialiseWebPush('com_example', 'vapidKey');
	}
}
```

### The `joomla.asset.json` file

Both scripts are required.

The `webpush` script handles the push notification subscription and unsubscription user interface.

The `webpush-worker` script handles displaying the notifications in the user's browser.

```json
{
  "$schema": "https://developer.joomla.org/schemas/json-schema/web_assets.json",
  "name": "com_example",
  "version": "1.0.0",
  "description": "An example component",
  "license": "GPL-2.0-or-later",
  "assets": [
    {
      "name": "com_example.webpush",
      "type": "script",
      "uri": "com_example/webpush.min.js",
      "dependencies": [
        "core"
      ],
      "attributes": {
        "defer": true
      }
    },
    {
      "name": "com_example.webpush-worker",
      "type": "script",
      "uri": "com_example/webpush-worker.min.js"
    }
  ]
}
```

### The HTML View code

Required. This code configures and loads the Web Push user interface JavaScript. 

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

### The view template `default.php`

Note that we start with both buttons being invisible and disabled and the unavailable message being invisible.

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

### Language strings

```ini
COM_EXAMPLE_WEBPUSH_HEAD="Web Push Demo"
COM_EXAMPLE_WEBPUSH_BTN_SUBSCRIBE="Enable Push Notifications"
COM_EXAMPLE_WEBPUSH_BTN_UNSUBSCRIBE="Disable Push Notifications"
COM_EXAMPLE_WEBPUSH_LBL_UNAVAILABLE_HEAD="Push notifications not available"
COM_EXAMPLE_WEBPUSH_LBL_UNAVAILABLE_BODY="Your browser does not support Web Push. Web Push is required to send push notifications. Please use a recent version of a browser supporting Web Push such as Edge, Chrome, Opera, or Firefox."
```