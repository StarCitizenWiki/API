<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 26.07.2018
 * Time: 13:22
 */

namespace App\Transformers\Api;

/**
 * Interface LocaleAwareTransformerInterface
 */
interface LocaleAwareTransformerInterface
{
    /**
     * @param string $localeCode
     *
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     *
     * @return void
     */
    public function setLocale(string $localeCode);
}
