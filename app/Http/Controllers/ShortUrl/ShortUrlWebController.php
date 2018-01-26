<?php declare(strict_types = 1);

namespace App\Http\Controllers\ShortUrl;

use App\Events\UrlShortened;
use App\Http\Controllers\Controller;
use App\Models\ShortUrl\ShortUrl;
use App\Models\ShortUrl\ShortUrlWhitelist;
use App\Rules\ShortUrlWhitelisted;
use App\Traits\TransformsDataTrait as TransformsData;
use App\Transformers\ShortUrl\ShortUrlTransformer;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Class ShortUrlController
 *
 * @package App\Http\Controllers\ShortUrl
 */
class ShortUrlWebController extends Controller
{
    use TransformsData;

    /**
     * ShortUrlController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->transformer = new ShortUrlTransformer();
        $this->middleware('throttle', ['except' => ['showShortUrlView', 'showResolveView']]);
    }

    /**
     * Returns the ShortUrl Index View
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showShortUrlView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        Cache::put(
            'short_url_whitelisted_domains',
            ShortUrlWhitelist::all()->sortBy('url')->where('internal', false),
            CACHE_TIME * 6
        );

        return view('shorturl.index')->with(
            'whitelistedUrls',
            Cache::get('short_url_whitelisted_domains')
        );
    }

    /**
     * Returns the ShortUrl resolve Web View
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function showResolveView(): View
    {
        app('Log')::info(make_name_readable(__FUNCTION__));

        return view('shorturl.resolve');
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addUrl(Request $request)
    {
        $url = ShortUrl::query()->where('url', $request->get('url'))->first();
        if (!is_null($url)) {
            return redirect()->route('shorturl.index')->withInput(
                [
                    'url' => config('app.shorturl_url').'/'.$url->hash,
                ]
            )->with('success', true);
        }

        $data = $this->validate(
            $request,
            [
                'url'        => [
                    'required',
                    'url',
                    'max:255',
                    'unique:short_urls',
                    new ShortUrlWhitelisted(),
                ],
                'hash'       => 'nullable|alpha_dash|max:32|unique:short_urls',
                'expired_at' => 'nullable|date|after:'.Carbon::now(),
            ]
        );

        if (!is_null($data['expired_at'])) {
            $data['expired_at'] = Carbon::parse($data['expired_at']);
        }

        if (is_null($data['hash'])) {
            $data['hash'] = ShortUrl::generateShortUrlHash();
        }

        $data['user_id'] = SHORT_URL_DEFAULT_USER_ID;

        $url = ShortUrl::create($data);

        event(new UrlShortened($url));

        return redirect()->route('shorturl.index')->withInput(
            [
                'url' => config('app.shorturl_url').'/'.$url->hash,
            ]
        );
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resolveUrl(Request $request)
    {
        $b = $url = str_replace([config('app.shorturl_url'), '/'], '', $request->get('url'));

        try {
            $url = ShortUrl::query()->where('hash', $url)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return back()->withErrors(__('Url nicht gefunden'.$b));
        }

        return redirect()->route('shorturl.web.resolve_form')->withInput(['url' => $url->url]);
    }

    /**
     * @param \App\Models\ShortUrl\ShortUrl $url
     *
     * @return $this|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function redirectToUrl(ShortUrl $url)
    {
        if ($url->user()->isBlacklisted()) {
            return back()->withErrors(__('Ersteller der Url ist gesperrt'));
        }

        if ($url->expired()) {
            return back()->withErrors(__('Url ist nicht mehr gültig'));
        }

        return redirect($url->url, 301);
    }
}
