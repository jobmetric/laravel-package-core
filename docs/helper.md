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

- [Next To Boolean Status](https://github.com/jobmetric/laravel-package-core/blob/master/docs/boolean-status.md)
