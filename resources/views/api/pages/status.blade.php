@extends('api.layouts.default')

{{-- Page Title --}}
@section('title', __('Status'))

{{-- Page Content --}}
@section('content')
    <div class="card status">
        <h4 class="card-header">@lang('Status')</h4>
        <div class="card-body">
            @foreach($notifications as $notification)
                <div class="card @if($notification->expired()) text-muted @else bg-{{ $notification->getBootstrapClass() }}@endif mb-4">
                    <h4 class=" card-header @if($notification->expired()) text-muted @else text-light @endif">
                        @component('components.elements.icon', ['class' => 'mr-2'])
                            {{ $notification->getIcon() }}
                        @endcomponent
                        @lang(\App\Models\Notification::NOTIFICATION_LEVEL_TYPES[$notification->level])
                        <small class="float-right">{{ $notification->created_at->format('d.m.Y H:i:s') }}</small>
                    </h4>
                    <div class="card-body bg-white">
                        {{ $notification->content }}
                    </div>
                </div>
            @endforeach
        </div>
        <nav class="card-footer">{{ $notifications->links() }}</nav>
    </div>
@endsection