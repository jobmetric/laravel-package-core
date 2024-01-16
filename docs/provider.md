[Back To README.md](https://github.com/jobmetric/laravel-package-core/blob/master/README.md)

# Introduction to Package Core Service Provider

The `ServiceProvider` class is the central place of all Laravel package bootstrapping. Your own packages and plugins can extend this class to hook into the Laravel application lifecycle.

We have a class here that makes coding inside other packages easier, and you can easily get rid of writing `ServiceProvider` for your packages with this class.

Well, what is the story about?

When you want to write a package, you have to write a `ServiceProvider` class for it, and then you have to register it in the `config/app.php` file. This is a bit of a hassle, and it's a bit of a hassle to write a `ServiceProvider` class for each package.

This class is designed to make it easier to write packages and to get rid of writing `ServiceProvider` for each package.

### Example

Suppose you want to write a package named `laravel-flow` and you want to write a `ServiceProvider` class for it. You have to write a class like this:

```php
namespace JobMetric\Flow;

use JobMetric\Flow\Services\FlowManager;
use JobMetric\PackageCore\PackageCore;
use JobMetric\PackageCore\PackageCoreServiceProvider;
use JobMetric\Translation\TranslationServiceProvider;

class FlowServiceProvider extends PackageCoreServiceProvider
{
    public function configuration(PackageCore $package): void
    {
        $package
            ->name('laravel-flow')
            ->hasConfig()
            ->hasMigration()
            ->hasRoute()
            ->hasTranslation()
            ->hasView()
            ->registerDependencyPublishable(TranslationServiceProvider::class)
            ->registerCommand(Commands\MakeFlow::class);
            ->registerClass('Flow', FlowManager::class);
    }
}
```

And that's it, your service provider package is ready.

### How to use

When you inherit your service provider class from `PackageCoreServiceProvider`, you have to implement the `configuration` method in it. This method is called once when the package is loaded, and you have to do all the settings in it.

In this method, you have to do all the settings for your package, such as registering commands, registering classes, registering dependencies, and so on.

### Methods

#### name

This method is used to set the package name. This name is used to register the package in the Laravel container service, so choose it carefully.

You can use the word `laravel-` at the beginning of your package, and for this reason, you have chosen a more beautiful package name.

```php
$package->name('laravel-flow');
```

#### hasConfig

This method is employed to register your package's configuration file. Pay attention to your package's folder structure, ensuring a `config` folder resides next to the `src` folder.

The configuration file should be named either `config.php` or `flow.php`, with a preference for `config.php`.

Additionally, the function accepts an input parameter through which you can specify other available config files within the same folder—though this is a rare occurrence

```php
$package->hasConfig();
```

#### hasMigration

This method is used to register your package migration files. Pay attention to your package folder structure, make sure there is a `database/migrations` folder next to the `src` folder.

Create your migration files and if you click the Laravel migration command, these files will be executed automatically. so easily

```php
$package->hasMigration();
```

#### hasView

This method is used to register your package view files. Pay attention to your package folder structure, make sure there is a `resources/views` folder next to the `src` folder.

```php
$package->hasView($publishable = false);
```

> The `$publishable` parameter is optional and is used to specify the path of view files in the application. If the value of this parameter is true, the view files will be published in the `resources/views/vendor/package-short-name` folder.

#### hasAsset

This method is used to register your package asset files. Pay attention to your package folder structure, make sure there is a `assets` folder next to the `src` folder.

```php
$package->hasAsset();
```

#### hasRoute

This method is employed to register your package's path files. Pay close attention to your package's folder structure, ensuring the existence of a "routes" folder next to the src folder.

Generate your path file, and if you have the specified function in your settings, these file will be executed automatically—making the process seamless.

The routes file should be named 'route.php'.

```php
$package->hasRoute();
```

#### hasTranslation

This method is used to register the translation files of your package. Pay close attention to your package folder structure and make sure there is a `lang` folder next to the `src` folder.

```php
$package->hasTranslation();
```

You can also use the following example inside the package.

```php
__('flow::base.flow')
```

#### registerCommand

This method is used to register the commands of your package. With this method, you register each command class separately.

```php
$package->registerCommand(Commands\MakeFlow::class);
```

#### registerClass

This method is used to register your package classes. With this method, you register each class separately in the Laravel container service.

- The first parameter is a key or alias for your class 
- The second parameter refers to the corresponding class 
- The third parameter is the type of existence of this class in the service container, which can be one of the following values: `singleton`, `bind`, `instance` (Normally it is on bind)

```php
$package->registerClass('Flow', FlowManager::class);
```

#### registerPublishable

Within the service provider, there's an option to dispatch a set of contents to the core of the application from within the package. But why do we do this? It's to allow users of your package to override your files, utilizing the variables you've defined in a different manner or for other customization purposes.

While some of these files are automatically included through functions like hasConfig, you also have the flexibility to manually add your own files and specify their destination within the application.

- The first parameter specifies the paths
- The second parameter specifies the groups, which are in the form of an array

```php
$package->registerPublishable(['file or path in package' => 'file or path in application'], ['group']);
```

#### registerDependencyPublishable

If your package needs to be released before the release of another package, you can use this option. This option is used to register the publishable files of the package that your package depends on.

```php
$package->registerDependencyPublishable(TranslationServiceProvider::class);
```

### Events

You just need to call these functions in your `Service Provider` and stop in front of what is being done and rewrite these functions.

for example:

```php
public function beforeRegisterPackage()
{
    // Your code
}
```

The list of all events is given in the table below:

| Event                               | Description                                                                |
|-------------------------------------|----------------------------------------------------------------------------|
| `beforeRegisterPackage`             | This event is called before registering the package.                       |
| `afterRegisterPackage`              | This event is called after registering the package.                        |
| `beforeNewInstancePackage`          | This event is called before creating a new instance of the package.        |
| `afterNewInstancePackage`           | This event is called after creating a new instance of the package.         |
| `beforeBootPackage`                 | This event is called before booting the package.                           |
| `afterBootPackage`                  | This event is called after booting the package.                            |
| `runInConsolePackage`               | This event is called when the package is running in the console.           |
| `runInTestPackage`                  | This event is called when the package is running in the test.              |
| `runInWebPackage`                   | This event is called when the package is running in the web.               |
| `configLoadedPackage`               | This event is called when the package configuration is loaded.             |
| `migrationLoadedPackage`            | This event is called when the package migration is loaded.                 |
| `viewLoadedPackage`                 | This event is called when the package view is loaded.                      |
| `translationsLoadedPackage`         | This event is called when the package translation is loaded.               |
| `afterRegisterClassPackage`         | This event is called after registering the package class.                  |
| `afterRegisterCommandPackage`       | This event is called after registering the package command.                |
| `afterRegisterPublishablePackage`   | This event is called after registering the package publishable.            |
| `afterPublishableDependencyPackage` | This event is called after registering the package publishable dependency. |
| `afterPublishableConfigPackage`     | This event is called after registering the package publishable config.     |
| `afterPublishableMigrationPackage`  | This event is called after registering the package publishable migration.  |
| `afterPublishableViewPackage`       | This event is called after registering the package publishable view.       |
| `afterPublishableAssetPackage`      | This event is called after registering the package publishable asset.      |

- [Next To Enum To Array](https://github.com/jobmetric/laravel-package-core/blob/master/docs/enum.md)
