@component('mail::message')
    # @lang('mail/registered.hello')
    {{ __('mail/registered.intro', ['name' => config('app.name')]) }}

    **@lang('mail/registered.api_key'):** `{{ $user->api_token }}`

    @component('mail::button', ['url' => config('app.api_url')])
        @lang('mail/registered.documentation')
    @endcomponent


    {{ config('app.name') }}
@endcomponent
