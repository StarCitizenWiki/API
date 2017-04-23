@component('mail::message')
    # @lang('mail/registered.hello')
    {{ __('mail/registered.intro', ['name' => config('app.name')]) }}
    @lang('mail/registered.change_password')

    **@lang('mail/registered.api_key'):** `{{ $user->api_token }}`

    **@lang('mail/registered.password'):** `{{ $password }}`

    @component('mail::button', ['url' => config('app.api_url')])
        @lang('mail/registered.documentation')
    @endcomponent

    <br>
    {{ config('app.name') }}
@endcomponent
