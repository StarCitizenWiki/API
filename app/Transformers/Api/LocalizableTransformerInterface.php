<?php

declare(strict_types=1);

namespace App\Transformers\Api;

/**
 * Interface LocaleAwareTransformerInterface
 */
interface LocalizableTransformerInterface
{
    public function setLocale(string $localeCode): void;

    public function getLocale(): ?string;
}
