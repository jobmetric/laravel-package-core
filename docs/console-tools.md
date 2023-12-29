[Back To README.md](https://github.com/jobmetric/laravel-package-core/blob/master/README.md)

# Introduction to Console Tools

This package provides a feature that can be used to make things faster in the command console.

## Methods

### getStub($path, $items)

This method returns the contents of the stub file.

> **$path:** The path to the stub file.
>
> **$items:** An array of items to be replaced in the stub file.

### putFile($path, $content)

This method creates a file with the contents of the stub file.

> **$path:** The path to the file.
> 
> **$content:** The contents of the file.

### isDir($path)

This method checks if the directory exists.

> **$path:** The path to the directory.

### makeDir($path)

This method creates a directory.

> **$path:** The path to the directory.

### isFile($path)

This method checks if the file exists.

> **$path:** The path to the file.

### message($message, $type)

This method displays a message in the console.

> **$message:** The message to be displayed.
> 
> **$type:** The type of message to be displayed. (`info`,`error`,`success`)