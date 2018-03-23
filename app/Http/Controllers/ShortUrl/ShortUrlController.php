<?php declare(strict_types = 1);

namespace App\Http\Controllers\ShortUrl;

use App\Http\Controllers\Controller;
use App\Models\ShortUrl\ShortUrl;

/**
 * Class ShortUrlWebController
 */
class ShortUrlController extends Controller
{
    /**
     * ShortUrlWebController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('throttle');
    }


    /**
     * @param \App\Models\ShortUrl\ShortUrl $url
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToUrlPreview(ShortUrl $url)
    {
        return view('shorturl.preview')->with(
            [
                'shorturl' => config('app.shorturl_url').'/'.$url->hash,
                'longurl' => $url->url,
            ]
        );
    }

    /**
     * @param \App\Models\ShortUrl\ShortUrl $url
     *
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function redirectToUrl(ShortUrl $url)
    {
        if ($url->expired()) {
            return back()->withErrors(__('Url ist nicht mehr gültig'));
        }

        return redirect($url->url, 301);
    }
}
