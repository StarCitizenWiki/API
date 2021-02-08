<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;
use Octfx\DeepLy\DeepLy;
use Octfx\DeepLy\Exceptions\AuthenticationException;
use Octfx\DeepLy\Exceptions\QuotaException;
use Octfx\DeepLy\Exceptions\RateLimitedException;
use Octfx\DeepLy\Exceptions\TextLengthException;
use Octfx\DeepLy\HttpClient\CallException;
use Octfx\DeepLy\Integrations\Laravel\DeepLyFacade;

final class TranslateText
{
    private string $text;

    public function __construct(string $text)
    {
        $this->text = $text;
    }

    /**
     * @param string $targetLocale
     * @param string $formality
     * @return string
     * @throws QuotaException
     * @throws RateLimitedException
     * @throws TextLengthException
     * @throws AuthenticationException
     */
    public function translate(string $targetLocale, string $formality = 'less'): string
    {
        $translation = '';

        try {
            if (mb_strlen($this->text) > DeepLy::MAX_TRANSLATION_TEXT_LEN) {
                foreach (str_split_unicode($this->text, DeepLy::MAX_TRANSLATION_TEXT_LEN) as $chunk) {
                    $chunkTranslation = DeepLyFacade::translate($chunk, $targetLocale, 'EN', $formality);
                    $translation .= " {$chunkTranslation}";
                }
            } else {
                $translation = DeepLyFacade::translate($this->text, $targetLocale, 'EN', $formality);
            }
        } catch (QuotaException $e) {
            app('Log')::warning('Deepl Quote exceeded!');
            throw $e;
        } catch (RateLimitedException $e) {
            app('Log')::info('Got rate limit exception. Trying job again in 60 seconds.');
            throw $e;
        } catch (TextLengthException $e) {
            app('Log')::warning($e->getMessage());
            throw $e;
        } catch (CallException | AuthenticationException | InvalidArgumentException $e) {
            app('Log')::warning(sprintf('%s: %s', 'Translation failed with Message', $e->getMessage()));
            throw $e;
        }

        return $translation;
    }
}
