@extends('api.layouts.default')

{{-- Page Title --}}
@section('title', __('Status'))

{{-- Page Content --}}
@section('content')
    <div class="card status">
        <h4 class="card-header">@lang('Status')</h4>
        <div class="card-body">
            @forelse($notifications as $notification)
                <div class="card @if($notification->expired()) text-muted @else bg-{{ $notification->getBootstrapClass() }}@endif mb-4">
                    <h4 class="card-header @if($notification->expired()) text-muted @else text-light @endif">
                        @component('components.elements.icon', ['class' => 'mr-2'])
                            {{ $notification->getIcon() }}
                        @endcomponent
                        @lang($notification->getLevelAsText())
                        <small class="float-right">{{ $notification->created_at->format('d.m.Y H:i:s') }}</small>
                    </h4>
                    <div class="card-body bg-white">
                        {{ $notification->content }}
                    </div>
                </div>
            @empty
                <div class="card bg-success mb-4">
                    <h4 class="card-header text-light">
                        @component('components.elements.icon', ['class' => 'mr-2'])
                            check-circle
                        @endcomponent
                        @lang('Keine Probleme gemeldet')
                        <small class="float-right">{{ now()->format('d.m.Y H:i:s') }}</small>
                    </h4>
                </div>
            @endforelse
        </div>
        <nav class="card-footer">{{ $notifications->links() }}</nav>
    </div>
@endsection