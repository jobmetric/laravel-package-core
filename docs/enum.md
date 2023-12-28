[Back To README.md](https://github.com/jobmetric/laravel-package-core/blob/master/README.md)

# Introduction to Enum To Array

This package provides a trait that can be used to convert an enum to an array.

## Methods

### names

This method returns an array of enum names.

```php
use JobMetric\PackageCore\Traits\EnumToArray;

enum Status
{
    use EnumToArray;

    const ACTIVE = 1;
    const INACTIVE = 2;
}

Status::names(); // ['ACTIVE', 'INACTIVE']
```

### values

This method returns an array of enum values.

```php
use JobMetric\PackageCore\Traits\EnumToArray;

enum Status
{
    use EnumToArray;

    const ACTIVE = 1;
    const INACTIVE = 2;
}

Status::values(); // [1, 2]
```

### array

This method returns an array of enum names and values.

```php
use JobMetric\PackageCore\Traits\EnumToArray;

enum Status
{
    use EnumToArray;

    const ACTIVE = 1;
    const INACTIVE = 2;
}

Status::array(); // ['ACTIVE' => 1, 'INACTIVE' => 2]
```

### arrayValues

This method returns an array of enum values and names.

```php
use JobMetric\PackageCore\Traits\EnumToArray;

enum Status
{
    use EnumToArray;

    const ACTIVE = 1;
    const INACTIVE = 2;
}

Status::arrayValues(); // [1 => 'ACTIVE', 2 => 'INACTIVE']
```

- [Next To Console Tools](https://github.com/jobmetric/laravel-package-core/blob/master/docs/console-tools.md)
