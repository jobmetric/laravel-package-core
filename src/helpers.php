<?php

use Illuminate\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

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
