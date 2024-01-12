[Back To README.md](https://github.com/jobmetric/laravel-package-core/blob/master/README.md)

# Introduction to Boolean Status

This package provides functionality that can be used for status fields that are boolean.

This feature is added to the model in the form of a trait, and you can equip your model with it.

```php
use JobMetric\PackageCore\Models\HasBooleanStatus;
```

## Methods

Two scope methods named `active` and `inactive` are added to your model, which you can use as in the example below.

```php
$users = User::active()->get();
```

```php
$users = User::inactive()->get();
```
