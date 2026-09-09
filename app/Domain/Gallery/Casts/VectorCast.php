<?php


namespace App\Domain\Gallery\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class VectorCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?array
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return array_map('floatval', $value);
        }

        $decoded = json_decode((string) $value, true);

        if (! is_array($decoded)) {
            throw new InvalidArgumentException("The {$key} value is not a valid vector.");
        }

        return array_map('floatval', $decoded);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if (! is_array($value)) {
            throw new InvalidArgumentException("The {$key} value must be an array.");
        }

        $vector = array_map(static function (mixed $component): float {
            if (! is_numeric($component) || ! is_finite((float) $component)) {
                throw new InvalidArgumentException('Vector components must be finite numbers.');
            }

            return (float) $component;
        }, $value);

        return json_encode($vector, JSON_THROW_ON_ERROR);
    }
}
