<?php

use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

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
     * @param EloquentBuilder|QueryBuilder $builder
     *
     * @return string
     */
    function queryToSql(EloquentBuilder|QueryBuilder $builder): string
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
