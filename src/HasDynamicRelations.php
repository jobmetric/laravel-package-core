<?php

namespace JobMetric\PackageCore;

trait HasDynamicRelations
{
    protected static array $dynamicRelations = [];

    /**
     * Add dynamic relation
     *
     * @param $name
     * @param $callback
     *
     * @return void
     */
    public static function addDynamicRelation($name, $callback): void
    {
        static::$dynamicRelations[$name] = $callback;
    }

    /**
     * Get dynamic relation
     *
     * @param $method
     * @param $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (isset(static::$dynamicRelations[$method])) {
            return call_user_func_array(static::$dynamicRelations[$method], [$this, $parameters]);
        }

        return parent::__call($method, $parameters);
    }

    /**
     * Get dynamic relation
     *
     * @param $method
     * @param $parameters
     *
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        if (isset(static::$dynamicRelations[$method])) {
            return call_user_func_array(static::$dynamicRelations[$method], [null, $parameters]);
        }

        return parent::__callStatic($method, $parameters);
    }
}
