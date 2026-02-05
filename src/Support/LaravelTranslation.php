<?php

namespace SchenkeIo\Invoice\Support;

use SchenkeIo\Invoice\Contracts\TranslationInterface;

/**
 * Laravel-specific implementation of the TranslationInterface.
 *
 * This class bridges the package's translation needs with Laravel's
 * built-in translation system, using the `__()` helper. It provides
 * a seamless integration for Laravel users, allowing them to use
 * the standard language files and localization features of the
 * framework within the invoicing system.
 */
class LaravelTranslation implements TranslationInterface
{
    /**
     * @param  array<string, mixed>  $replace
     */
    public function translate(string $key, array $replace = [], ?string $locale = null): string
    {
        $translation = __($key, $replace, $locale);
        if (is_string($translation)) {
            return $translation;
        }

        return $key;
    }
}
