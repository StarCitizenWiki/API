<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hannes
 * Date: 21.03.2017
 * Time: 09:50
 */

namespace App\Transformers\ShortURL;

use App\Models\ShortURL\ShortURL;
use App\Transformers\AbstractBaseTransformer;

/**
 * Class ShortURLTransformer
 *
 * @package App\Transformers\Tools
 */
class ShortURLTransformer extends AbstractBaseTransformer
{
    /**
     * Transformes a given ShortURL
     *
     * @param \App\Models\ShortURL\ShortURL $url URL to transform
     *
     * @return array
     */
    public function transform(ShortURL $url)
    {
        return [
            [
                'original_url' => $url->url,
                'hash_name'    => $url->hash_name,
                'url'          => 'https://'.config('app.shorturl_url').'/'.$url->hash_name,
            ],
        ];
    }
}
