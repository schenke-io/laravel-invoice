<?php

namespace SchenkeIo\Invoice\Contracts;

/**
 * Interface for translation services used within the invoice package.
 *
 * This interface defines the contract for translating keys with optional
 * replacements and locale specification, ensuring consistent translation
 * across the package. It allows for different translation backends to
 * be used, such as Laravel's native translation system or a custom
 * implementation, providing flexibility in how localized text is managed.
 */
interface TranslationInterface
{
    /**
     * @param  array<string, mixed>  $replace
     */
    public function translate(string $key, array $replace = [], ?string $locale = null): string;
}
