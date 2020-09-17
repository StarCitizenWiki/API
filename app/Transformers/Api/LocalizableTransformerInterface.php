<?php declare(strict_types = 1);

namespace App\Transformers\Api;

/**
 * Interface LocaleAwareTransformerInterface
 */
interface LocalizableTransformerInterface
{
    /**
     * @param string $localeCode
     *
     * @return void
     */
    public function setLocale(string $localeCode): void;
}
