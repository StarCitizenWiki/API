<?php

declare(strict_types=1);

namespace App\Jobs\Transcript\Translate;

use App\Models\Transcript\Transcript;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use InvalidArgumentException;
use StarCitizenWiki\DeepLy\Exceptions\TextLengthException;
use StarCitizenWiki\DeepLy\Integrations\Laravel\DeepLyFacade;

/**
 * Translate a singleTranscript.
 */
class TranslateTranscript implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private const DEEPL_MAX_LENGTH = 30000;

    /**
     * @var Transcript
     */
    private $transcript;

    /**
     * Create a new job instance.
     *
     * @param Transcript $transcript
     */
    public function __construct(Transcript $transcript)
    {
        $this->transcript = $transcript;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (null === $this->transcript->english()) {
            return;
        }

        app('Log')::info("Translating Transcript {$this->transcript->url}");

        $english = $this->transcript->english()->translation;
        $translation = '';

        try {
            if (mb_strlen($english) > self::DEEPL_MAX_LENGTH) {
                foreach ($this->strSplitUnicode($english, self::DEEPL_MAX_LENGTH) as $chunk) {
                    $chunkTranslation = DeepLyFacade::translate($chunk, 'DE', 'EN');
                    $translation .= " {$chunkTranslation}";
                }
            } else {
                $translation = DeepLyFacade::translate($english, 'DE', 'EN');
            }
        } catch (TextLengthException $e) {
            app('Log')::warning($e->getMessage());

            return;
        } catch (InvalidArgumentException $e) {
            app('Log')::warning(sprintf('%s: %s', 'Translation failed with Message', $e->getMessage()));

            $this->fail($e);

            return;
        }

        $this->transcript->translations()->updateOrCreate(
            [
                'locale_code' => 'de_DE',
            ],
            [
                'translation' => trim($translation),
                'proofread' => false,
            ]
        );
    }

    /**
     * Splits a Unicode String into the given length chunks.
     *
     * @param string $str
     * @param int    $length
     *
     * @return array|array[]|false|string[]
     */
    private function strSplitUnicode(string $str, int $length = 1)
    {
        $tmp = preg_split('~~u', $str, -1, PREG_SPLIT_NO_EMPTY);
        if ($length > 1) {
            $chunks = array_chunk($tmp, $length);
            foreach ($chunks as $i => $chunk) {
                $chunks[$i] = implode('', (array) $chunk);
            }
            $tmp = $chunks;
        }

        return $tmp;
    }
}
