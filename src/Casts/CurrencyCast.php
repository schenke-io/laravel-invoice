<?php

namespace SchenkeIo\Invoice\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use SchenkeIo\Invoice\Money\Currency;

/**
 * Eloquent custom cast for the Currency value object.
 *
 * This class allows Laravel models to automatically cast database values
 * (typically floats) into Currency value objects and vice-versa, ensuring
 * type safety and consistent monetary calculations within the model.
 *
 * @implements CastsAttributes<Currency,float>
 */
class CurrencyCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): Currency
    {
        return Currency::fromAny($value);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?float
    {
        return Currency::fromAny($value)->toFloat();
    }

    /**
     * Get the value that should be serialized.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function serialize(Model $model, string $key, mixed $value, array $attributes): string
    {
        $currency = Currency::fromAny($value);

        return "$currency";
    }
}
