<?php

namespace SchenkeIo\Invoice\Contracts;

/**
 * Interface for translation services used within the invoice package.
 *
 * This interface defines the contract for translating keys with optional
 * replacements and locale specification, ensuring consistent translation
 * across the package.
 */
interface TranslationInterface
{
    /**
     * @param  array<string, mixed>  $replace
     */
    public function translate(string $key, array $replace = [], ?string $locale = null): string;
}
