<?php

namespace JobMetric\PackageCore\Enums;

use BackedEnum;
use RuntimeException;
use UnitEnum;

/**
 * Trait EnumMacros
 *
 * A utility trait for working with PHP 8.1+ enums (UnitEnum and BackedEnum).
 * Adds helper methods for accessing enum names, values, labels, translations,
 * dynamic static calls, and JSON serialization.
 *
 * Example usage:
 *   - MyEnum::array()                   => [value => name]
 *   - MyEnum::arrayValues()            => [name => value]
 *   - MyEnum::arrayWithLabels()        => [value => label()]
 *   - MyEnum::arrayWithTranslations('enums.status') => [value => trans('enums.status.name')]
 *   - MyEnum::toArray('labels')        => list of label()
 *   - MyEnum::fromName('Draft')        => MyEnum::Draft
 *   - MyEnum::fromValue('draft')       => MyEnum::Draft
 *   - MyEnum::Draft()                  => 'draft' (via __callStatic)
 *
 * @mixin UnitEnum
 * @mixin BackedEnum
 * @method static static[] cases()
 */
trait EnumMacros
{
    /**
     * Ensure that the enum class supports the `cases()` method.
     *
     * @return void
     * @throws RuntimeException
     */
    protected static function ensureEnum(): void
    {
        if (!method_exists(static::class, 'cases')) {
            throw new RuntimeException(static::class . ' must be an enum to use EnumMacros.');
        }
    }

    /**
     * Get a list of all enum case names.
     *
     * @return list<string>
     */
    public static function names(): array
    {
        self::ensureEnum();

        return array_column(self::cases(), 'name');
    }

    /**
     * Get a list of all enum case values.
     *
     * @return list<mixed>
     * @throws RuntimeException
     */
    public static function values(): array
    {
        self::ensureEnum();

        if (!is_subclass_of(static::class, BackedEnum::class)) {
            throw new RuntimeException(static::class . ' must be a BackedEnum to use values().');
        }

        return array_column(self::cases(), 'value');
    }

    /**
     * Get an associative array [value => name].
     *
     * @return array<mixed, string>
     */
    public static function array(): array
    {
        return array_combine(self::values(), self::names());
    }

    /**
     * Get an associative array [name => value].
     *
     * @return array<string, mixed>
     */
    public static function arrayValues(): array
    {
        return array_combine(self::names(), self::values());
    }

    /**
     * Get an array of human-readable labels.
     * Falls back to case name if label() method is not defined.
     *
     * @return list<string>
     */
    public static function labels(): array
    {
        self::ensureEnum();

        return array_map(
            fn($case) => method_exists($case, 'label') ? $case->label() : $case->name,
            self::cases()
        );
    }

    /**
     * Get an associative array [value => label].
     *
     * @return array<mixed, string>
     */
    public static function arrayWithLabels(): array
    {
        return array_combine(self::values(), self::labels());
    }

    /**
     * Get an associative array [value => translated label] using Laravel translation files.
     *
     * @param string $translationPrefix The base key, e.g. "enums.status"
     * @return array<mixed, string>
     */
    public static function arrayWithTranslations(string $translationPrefix): array
    {
        self::ensureEnum();

        return array_combine(
            self::values(),
            array_map(
                fn($case) => __("$translationPrefix.{$case->name}"),
                self::cases()
            )
        );
    }

    /**
     * Convert enum to array in different formats.
     *
     * Supported formats:
     *   - value_name  => [value => name]
     *   - name_value  => [name => value]
     *   - value_label => [value => label()]
     *   - labels      => list of label()s
     *
     * @param string $format
     * @return array
     */
    public static function toArray(string $format = 'value_name'): array
    {
        return match ($format) {
            'value_name' => self::array(),
            'name_value' => self::arrayValues(),
            'value_label' => self::arrayWithLabels(),
            'labels' => self::labels(),
            default => throw new RuntimeException("Unsupported enum array format: $format"),
        };
    }

    /**
     * Get enum case by name.
     *
     * @param string $name
     * @return static|null
     */
    public static function fromName(string $name): ?static
    {
        self::ensureEnum();

        foreach (self::cases() as $case) {
            if ($case->name === $name) {
                return $case;
            }
        }

        return null;
    }

    /**
     * Get enum case by value.
     *
     * @param mixed $value
     * @return static|null
     */
    public static function fromValue(mixed $value): ?static
    {
        self::ensureEnum();

        if (!is_subclass_of(static::class, BackedEnum::class)) {
            return null;
        }

        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case;
            }
        }

        return null;
    }

    /**
     * Allow dynamic static method calls like Status::Published()
     * to return the value of the enum case.
     *
     * @param string $name
     * @param array $arguments
     * @return mixed|null
     */
    public static function __callStatic(string $name, array $arguments)
    {
        self::ensureEnum();

        return self::arrayValues()[$name] ?? null;
    }

    /**
     * Get JSON-encoded enum [value => label].
     *
     * @return string
     */
    public static function json(): string
    {
        return json_encode(self::arrayWithLabels(), JSON_UNESCAPED_UNICODE);
    }
}
