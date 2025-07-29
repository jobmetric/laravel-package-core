<?php

namespace JobMetric\PackageCore\Enums;

/**
 * @method static BIND()
 * @method static SINGLETON()
 * @method static SCOPED()
 * @method static REGISTER()
 */
enum RegisterClassTypeEnum: string
{
    use EnumMacros;

    case BIND = "bind";
    case SINGLETON = "singleton";
    case SCOPED = "scoped";
    case REGISTER = "register";
}
