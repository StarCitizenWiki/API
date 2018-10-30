<?php declare(strict_types = 1);

namespace App\Jobs\Rsi\CommLink\Translate;

use App\Models\Rsi\CommLink\CommLink;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use StarCitizenWiki\DeepLy\Exceptions\TextLengthException;
use StarCitizenWiki\DeepLy\Integrations\Laravel\DeepLyFacade;

/**
 * Translate a single Comm-Link
 */
class TranslateCommLink implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    const DEEPL_MAX_LENGTH = 30000;

    /**
     * @var \App\Models\Rsi\CommLink\CommLink
     */
    private $commLink;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Rsi\CommLink\CommLink $commLink
     */
    public function __construct(CommLink $commLink)
    {
        $this->commLink = $commLink;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app('Log')::info("Translating Comm-Link with ID {$this->commLink->cig_id}");

        if (null === optional($this->commLink->german())->translation) {
            $english = $this->commLink->english()->translation;
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
            } catch (\InvalidArgumentException $e) {
                app('Log')::warning(sprintf('%s: %s', 'Translation failed with Message', $e->getMessage()));

                $this->fail($e);

                return;
            }

            $this->commLink->translations()->updateOrCreate(
                [
                    'locale_code' => 'de_DE',
                ],
                [
                    'translation' => trim($translation),
                    'proofread' => false,
                ]
            );
        }
    }

    /**
     * Splits a Unicode String into the given length chunks
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
                $chunks[$i] = join('', (array) $chunk);
            }
            $tmp = $chunks;
        }

        return $tmp;
    }
}
