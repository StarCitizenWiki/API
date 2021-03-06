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

    /**
     * Replace some wrong translations
     *
     * @param string $translation
     * @return string
     */
    public static function runTextReplacements(string $translation): string
    {
        $replacements = collect([
            'Geschenke der Sternenbürger' => 'Star Citizen Geschenke',
            'Überlieferungen der Sternenbürger' => 'Überlieferungen von Star Citizen',
            'im Sternenbürger TAG' => 'in Star Citizen TAG',
            'Woche im Sternenbürger' => 'Woche in Star Citizen',
            'Sternenbürger Live' => 'Star Citizen Live',
            'Sternenbürger-Community' => 'Star Citizen Community',
            'der Innere Sternenbürger' => 'Inside Star Citizen',
            'Im Inneren von Star Citizen' => 'Inside Star Citizen',
            'Gemeinschaft der Sternenbürger' => 'Star Citizen Community',
            'der Sternenbürger-Community' => 'der Star Citizen Community',
            'für alle Sternenbürger' => 'für alle Star Citizen',
            'Sternenbürger live' => 'Star Citizen live',


            'der Sternenbürger' => 'Star Citizen',
            'zur Sternenbürgerkunde' => 'zur Star Citizen Lore',
            'von Sternenbürger' => 'von Star Citizen',
            'den Sternenbürger' => 'Star Citizen',
            'im Sternenbürger' => 'Star Citizen',
            'des Sternenbürgers' => 'von Star Citizen',

            'Sternenbürger' => 'Star Citizen',
            'Sternenbürgern' => 'Star Citizen',

            'Grüße Bürgerinnen und Bürger' => '',
            'Grüße Bürger' => 'Grüße Citizens',

            'den Vers' => 'das Verse',

            'Staffel 42' => 'Squadron 42',
        ]);


        $replacements->each(function (string $to, string $from) use (&$translation) {
            $translation = str_replace($from, $to, $translation);
        });

        return $translation;
    }
}
