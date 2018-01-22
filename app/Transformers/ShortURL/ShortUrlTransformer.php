<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hannes
 * Date: 21.03.2017
 * Time: 09:50
 */

namespace App\Transformers\ShortUrl;

use App\Models\ShortUrl\ShortUrl;
use App\Transformers\AbstractBaseTransformer;

/**
 * Class ShortUrlTransformer
 *
 * @package App\Transformers\Tools
 */
class ShortUrlTransformer extends AbstractBaseTransformer
{
    /**
     * Transforms a given ShortUrl
     *
     * @param \App\Models\ShortUrl\ShortUrl $url URL to transform
     *
     * @return array
     */
    public function transform(ShortUrl $url)
    {
        return [
            [
                'original_url' => $url->url,
                'hash'    => $url->hash,
                'url'          => 'https://'.config('app.shorturl_url').'/'.$url->hash,
            ],
        ];
    }
}
