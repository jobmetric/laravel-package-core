<?php

use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
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
            $path = base_path(str_replace('\\', DIRECTORY_SEPARATOR, $namespace));

            if (File::exists($path)) {
                $files = File::allFiles($path);

                foreach ($files as $file) {
                    if ($file->getExtension() === 'php') {
                        $filename = $file->getFilenameWithoutExtension();

                        if ($suffix === '' || str_ends_with($filename, $suffix)) {
                            $result[] = $namespace . '\\' . $filename;
                        }
                    }
                }
            }
        }

        return $result;
    }
}
