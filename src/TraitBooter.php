<?php

namespace JobMetric\PackageCore;

trait TraitBooter
{
    /**
     * Bootstrap the service type and its traits.
     *
     * @return void
     */
    protected function boot(): void
    {
        static::beforeBoot();

        $class = static::class;

        foreach (class_uses_recursive($class) as $trait) {
            // Build method names based on the trait name
            $staticMethod = 'staticBoot' . class_basename($trait);
            $nonStaticMethod = 'boot' . class_basename($trait);

            // Call the static method if it exists
            if (method_exists($class, $staticMethod)) {
                forward_static_call([$class, $staticMethod]);
            }

            // Call the non-static method if it exists
            if (method_exists($class, $nonStaticMethod)) {
                $this->{$nonStaticMethod}();
            }
        }

        static::afterBoot();
    }

    /**
     * Perform any actions required before the service type boots.
     *
     * @return void
     */
    protected function beforeBoot(): void
    {
        //
    }

    /**
     * Perform any actions required after the service type boots.
     *
     * @return void
     */
    protected function afterBoot(): void
    {
        //
    }
}
