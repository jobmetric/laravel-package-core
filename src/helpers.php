<?php

use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

if (!function_exists('appNamespace')) {
    /**
     * Get the application namespace for the application.
     *
     * @return string
     */
    function appNamespace(): string
    {
        try {
            return Container::getInstance()
                ->make(Application::class)
                ->getNamespace();
        } catch (Throwable) {
            return 'App\\';
        }
    }
}

if (!function_exists('appFolderName')) {
    /**
     * Get the application folder name for the application.
     *
     * @return string
     */
    function appFolderName(): string
    {
        return basename(app_path());
    }
}

if (!function_exists('queryToSql')) {
    /**
     * get full sql query string in query builder
     *
     * @param object $builder
     *
     * @return string
     */
    function queryToSql(object $builder): string
    {
        return vsprintf(str_replace('?', '%s', str_replace('?', "'?'", $builder->toSql())), $builder->getBindings());
    }
}

if (!function_exists('checkDatabaseConnection')) {
    /**
     * check database connection
     *
     * @return bool
     */
    function checkDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}

if (!function_exists('shortFormatNumber')) {
    /**
     * short format number
     *
     * @param string $number
     * @param int $precision
     *
     * @return string
     */
    function shortFormatNumber(string $number, int $precision = 1): string
    {
        if (!is_numeric($number)) {
            throw new InvalidArgumentException("Input must be a numeric value.");
        }

        $number = (float)$number;

        if ($number < 1000) {
            return $number;
        }

        $units = ['', 'K', 'M', 'B', 'T', 'P', 'E', 'Z', 'Y'];
        $power = floor(log($number, 1000));
        $shortNumber = $number / pow(1000, $power);

        return round($shortNumber, $precision) . $units[$power];
    }
}

if (!function_exists('getServiceTypeClass')) {
    /**
     * get service type class
     *
     * @param string $className
     *
     * @return mixed
     */
    function getServiceTypeClass(string $className): mixed
    {
        $className = explode("\\", $className);
        $className = end($className);
        $typeClass = "{$className}Type";

        return app($typeClass);
    }
}

if (!function_exists('resolveNamespacePath')) {
    /**
     * Resolve the file system path of a given namespace.
     *
     * @param string $namespace
     * @return string|null
     */
    function resolveNamespacePath(string $namespace): ?string
    {
        $composerJsonPath = base_path('composer.json');

        if (!file_exists($composerJsonPath)) {
            return null;
        }

        $composerData = json_decode(file_get_contents($composerJsonPath), true);
        $psr4Mappings = array_merge(
            $composerData['autoload']['psr-4'] ?? [],
            $composerData['autoload-dev']['psr-4'] ?? []
        );

        foreach ($psr4Mappings as $prefix => $path) {
            if (str_starts_with($namespace, trim($prefix, '\\'))) {
                $relativeNamespace = str_replace($prefix, '', $namespace);
                $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativeNamespace);

                return str_replace('/', DIRECTORY_SEPARATOR, base_path(trim($path, '/') . DIRECTORY_SEPARATOR . $relativePath));
            }
        }

        $composerVendorPath = base_path('vendor/composer/autoload_psr4.php');
        if (file_exists($composerVendorPath)) {
            $vendorPsr4Mappings = include $composerVendorPath;

            foreach ($vendorPsr4Mappings as $prefix => $paths) {
                if (str_starts_with($namespace, trim($prefix, '\\'))) {
                    $relativeNamespace = str_replace($prefix, '', $namespace);
                    $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $relativeNamespace);

                    foreach ((array) $paths as $vendorPath) {
                        $fullPath = rtrim($vendorPath, '/') . DIRECTORY_SEPARATOR . $relativePath;
                        $fullPath = str_replace('/', DIRECTORY_SEPARATOR, $fullPath);
                        if (file_exists($fullPath)) {
                            return $fullPath;
                        }
                    }
                }
            }
        }

        return null;
    }
}

if (!function_exists('getDriverNames')) {
    /**
     * get driver names
     *
     * @param array  $namespaces
     * @param string $suffix
     *
     * @return array
     */
    function getDriverNames(array $namespaces, string $suffix = ''): array
    {
        $result = [];

        foreach ($namespaces as $namespace) {
            // Resolve the base path for the namespace
            $path = resolveNamespacePath($namespace);

            if ($path && File::exists($path)) {
                $files = File::allFiles($path);

                foreach ($files as $file) {
                    if ($file->getExtension() === 'php') {
                        $filename = $file->getFilenameWithoutExtension();

                        if ($suffix === '' || str_ends_with($filename, $suffix)) {
                            $result[] = rtrim($namespace, '\\') . '\\' . $filename;
                        }
                    }
                }
            }
        }

        return $result;
    }
}

if (!function_exists('loadMigrationPath')) {
    /**
     * Load migrations from a specified path.
     *
     * @param string $path
     *
     * @return void
     */
    function loadMigrationPath(string $path): void
    {
        foreach (glob($path . '/*.php') as $file) {
            $migration = include $file;
            if ($migration instanceof Migration) {
                $migration->up();
            }
        }
    }
}

if (!function_exists('hasPropertyInClass')) {
    /**
     * Check if a class has a specific property defined in its own class scope.
     *
     * @param mixed $object
     * @param string $property
     *
     * @return bool
     * @throws Throwable
     */
    function hasPropertyInClass(mixed $object, string $property): bool
    {
        $reflection = new ReflectionClass($object);

        if ($reflection->hasProperty($property)) {
            $declaringClass = $reflection->getProperty($property)->getDeclaringClass()->getName();
            return $declaringClass === $reflection->getName();
        }

        return false;
    }
}
