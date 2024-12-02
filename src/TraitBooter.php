<?php

namespace JobMetric\PackageCore;

trait TraitBooter
{
    /**
     * Bootstrap the service type and its traits.
     *
     * @return void
     */
    protected static function boot(): void
    {
        static::beforeBoot();

        $class = static::class;

        foreach (class_uses_recursive($class) as $trait) {
            $method = 'boot' . class_basename($trait);

            if (method_exists($class, $method)) {
                forward_static_call([$class, $method]);
            }
        }

        static::afterBoot();
    }

    /**
     * Perform any actions required before the service type boots.
     *
     * @return void
     */
    protected static function beforeBoot(): void
    {
        //
    }

    /**
     * Perform any actions required after the service type boots.
     *
     * @return void
     */
    protected static function afterBoot(): void
    {
    }
}
