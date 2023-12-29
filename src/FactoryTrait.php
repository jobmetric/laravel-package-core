<?php

namespace JobMetric\PackageCore;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

trait FactoryTrait
{
    /**
     * merge package factory
     *
     * @return void
     */
    public function factoryResolver(): void
    {
        Factory::guessFactoryNamesUsing(function (string $modelName) {
            $namespace = 'Database\\Factories\\';
            $appScope = appNamespace();

            if (Str::startsWith($modelName, $appScope . 'Models\\')) {
                $modelName = Str::after($modelName, $appScope . 'Models\\');
            } else {
                $array = explode('\\', $modelName);

                if (count($array) > 2) {
                    $appScope = $array[0] . '\\' . $array[1] . '\\';
                    $namespace = $appScope . 'Factories\\';

                    $modelName = Str::after($modelName, $appScope . 'Models\\');
                } else {
                    $modelName = Str::after($modelName, $appScope);
                }
            }

            return $namespace . $modelName . 'Factory';
        });
    }
}
