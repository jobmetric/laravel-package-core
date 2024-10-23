[Back To README.md](https://github.com/jobmetric/laravel-package-core/blob/master/README.md)

# Introduction to Helper Functions

This package provides helper functions that can be used by other packages and programs.

## Methods

### appNamespace()

This method returns the application namespace.

```php
appNamespace(); // return 'App\\'
```

### queryToSql($query)

This method returns the SQL query of a query builder.

```php
$query = DB::table('users')->where('votes', '>', 100);
queryToSql($query); // return 'select * from `users` where `votes` > 100'
```

### checkDatabaseConnection()

This method checks the database connection.

```php
checkDatabaseConnection(); // return true or false
```

### shortFormatNumber($number, $precision = 2)

This method returns a short format of a number.

```php
shortFormatNumber(1000); // return '1K'
shortFormatNumber(1000000); // return '1M'
shortFormatNumber(1000000000); // return '1B'
shortFormatNumber(1000000000000); // return '1T'
```

- [Next To Boolean Status](https://github.com/jobmetric/laravel-package-core/blob/master/docs/boolean-status.md)
