<?php

declare(strict_types=1);

namespace App\Traits;

use App\Exceptions\Translation\AuthenticationException;
use App\Exceptions\Translation\QuotaExceededException;
use App\Exceptions\Translation\RateLimitException;
use App\Exceptions\Translation\TranslationException;

/**
 * Shared DeepL exception ladder for translation jobs
 */
trait HandlesTranslationExceptions
{
    /**
     * Wrap a translation call in the shared exception ladder.
     *
     * @param  callable(): string  $translate
     * @param  array<string, mixed>  $context  log context identifying the translated item
     * @return string|false|null the translation, false when the job was failed or released, null when the item was skipped
     */
    protected function translateOrAbort(callable $translate, array $context = []): string|false|null
    {
        try {
            return $translate();
        } catch (QuotaExceededException $e) {
            app('Log')::warning('DeepL quota exceeded');

            $this->fail($e);

            return false;
        } catch (RateLimitException $e) {
            app('Log')::info('Got rate limit exception. Trying job again in 60 seconds.');

            $this->release(60);

            return false;
        } catch (AuthenticationException $e) {
            app('Log')::error('DeepL authentication failed', ['error' => $e->getMessage()]);

            $this->fail($e);

            return false;
        } catch (TranslationException $e) {
            app('Log')::warning('Translation failed', $context + ['error' => $e->getMessage()]);

            return null;
        }
    }
}
