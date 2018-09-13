@extends('admin.layouts.full_width')

@section('body--class', 'bg-dark')

{{-- Page Title --}}
@section('title', __('Lizenz Akzeptieren'))

@section('topNav--class', 'd-none')

@section('main--class', 'mt-5')

@section('content')
    @component('components.heading', [
        'class' => 'text-center mb-5',
        'route' => url('/'),
    ])@endcomponent

    <div class="card bg-dark text-light-grey">
        <h4 class="card-header">@lang('Editor Lizenz akzeptieren')</h4>
        <div class="card-body">
            <p>
                {{-- TODO Update Text, Link zum Wiki Artikel --}}
                @lang('Durch den Klick auf "Bestätigen" bestätigst du, dass jegliche von dir übersetzten Texte der Allgemeinheit frei zur Verfügung stehen, und du keine Rechte an diesen hast.')
            </p>
            @component('components.forms.form', [
                'action' => route('web.admin.accept_licence')
            ])
                <button class="btn btn-secondary btn-block mb-3">@lang('Bestätigen')</button>
            @endcomponent
            <small><a href="{{ route('web.api.index') }}" class="text-white">@lang('Zurück')</a></small>
        </div>
    </div>
@endsection