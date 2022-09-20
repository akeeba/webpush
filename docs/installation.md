## Installation

There are two ways to install Akeeba Web Push: through [Composer](https://getcomposer.org/) or by copying files.

### Using Composer

_Note: This is subject to this library being listed on Packagist_

Add `akeeba/webpush` as a requirement to your Composer dependencies:
```bash
composer require akeeba/webpush
```

As long as your extension loads the Composer autoloader you're set.

### Copying files

If you are not familiar with Composer, or if you would rather change the namespace of the library (e.g. through [Rector](https://getrector.org/)), you can instead copy files to your component.

Create a new top-level folder named `webpush` in your component and copy the content's of this repository's [src](../src) folder into it.

Change your service provider (`services/provider.php`) to autoload the library by adding this line in your `ComponentInterface` service definition:
```php
JLoader::registerNamespace('Akeeba\\WebPush', __DIR__ . '/../webpush');
```

Here is an example service provide so you can see where this line is meant to be added:
```php
return new class implements ServiceProviderInterface {
	public function register(Container $container)
	{
		$container->registerServiceProvider(new MVCFactory('Acme\\Example'));
		$container->registerServiceProvider(new ComponentDispatcherFactory('Acme\\Example'));

		$container->set(
			ComponentInterface::class,
			function (Container $container) {
			    // !!! THIS IS WHERE WE LOAD AKEEBA WEB PUSH !!!
				JLoader::registerNamespace('Akeeba\\WebPush', __DIR__ . '/../webpush');

				$component = new ExampleComponent($container->get(ComponentDispatcherFactoryInterface::class));

				$component->setMVCFactory($container->get(MVCFactoryInterface::class));

				return $component;
			}
		);
	}
};
```

**Important!** Remember to add the `webpush` folder in your component's XML manifest!

### Dos and Don'ts

If you are using Composer to load this library **DO NOT** load the Composer `autoload.php` outside of your component's context. That is to say, only include the `autoload.php` in your Dispatcher. If this is not the case, use the file copy method.

If you are using the file copy method **DO NOT** put the `JLoader::registerNamespace` line outside your `ComponentInterface` definition (see the example above).

If you intend to use this library in plugins — including System, ActionLog or Scheduled Task plugins, **DO NOT** use the library unmodified. Instead, you need to do two things:
1. Use the “Copying files” method described above
2. Change the namespace from `Akeeba\WebPush` to something specific to your component, e.g. `Acme\Example\WebPush`. You can do that by manually searching and replacing the strings or with an automated tool like Rector.

If you do not heed this advice you will be leaking your version of Akeeba Web Push to the entire CMS, regardless of which component is running. If another component expects a _different_ version of the library you will make it impossible for it to use the version it expects. This will make me very distraught; I'll publicly name and shame the offending extensions as the reason we can't have nice things.