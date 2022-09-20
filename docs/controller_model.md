## Controller, Model and the `config.xml` file

Your users need to be able to subscribe and unsubscribe from Push Notifications on each of their devices and browsers (there is no method which will subscribe a user across all their devices and browsers). To do that, we need a Controller and a matching Model. We recommend calling these `PushController` and `PushModel` unless you already have a controller and / or model with that name.

The implementation of the Controller and Model code is provided in Traits. Therefore, if absolutely necessary, you can reuse an existing controller and / or model, simply adding the respective trait (and one line of code in the Model) to integrate with Akeeba Web Push.

### Controller

The PushController is a very simple affair:

```php
<?php
namespace Acme\Example\Administrator\Controller;

defined('_JEXEC') || die;

use Akeeba\WebPush\WebPushControllerTrait;
use Joomla\CMS\MVC\Controller\BaseController;

class PushController extends BaseController
{
	use WebPushControllerTrait;
}
```

If you want to send a sample push notification when the user subscribes to push notifications for the first time and possibly use a model which is not called `PushModel` this is the full version of the `PushController` class

```php
<?php
namespace Acme\Example\Administrator\Controller;

defined('_JEXEC') || die;

use Akeeba\WebPush\NotificationOptions;
use Akeeba\WebPush\WebPushControllerTrait;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Uri\Uri;

class PushController extends BaseController
{
	use WebPushControllerTrait;

	public function getModel($name = 'Whatever', $prefix = 'Administrator', $config = [])
	{
		return parent::getModel($name, $prefix, $config);
	}

    // This is optional. It sends an example push notification after a browser is subscribed to Web Push.
	protected function onAfterWebPushSaveSubscription(?object $subscription)
	{
		$options = new NotificationOptions();
		$options->body = 'Hello from com_example!';

		$this->getModel()->sendNotification(
			$this->app->get('sitename'),
			$options->toArray(),
			null,
			$subscription
		);
	}
}
```

### Model

The model is equally easy. You just need to use the `Akeeba\WebPush\WebPushModelTrait` and call the `initialiseWebPush` method in your constructor:

```php
<?php
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

        // This is required
		$this->initialiseWebPush('com_example', 'vapidKey');
	}
}
```

The two parameters to `initialiseWebPush` are the name of your component (e.g. `com_example`) and the key of the component option which will store the VAPID keys for WebPush. By default, the options key is called `vapidKey`.

_Note: VAPID keys is a set of automatically generated cryptographic keys which are used to make sure that only your site and your browser can see the contents of the notifications. Everyone else, including the company making the browser, will only see the encrypted form of the message, without any indication of what it is about._

**WARNING!** If the VAPID keys change, any existing push notifications subscriptions will stop working properly. DO NOT CHANGE THE VAPID KEYS and do offer a suitable way for your users to back them up and reinstall them should they mess up their database.

### Your `config.xml`

You need to add the following to your component's `config.xml` to let it store the VAPID keys:

```xml
<field
        name="vapidKey"
        type="hidden"
/>
```