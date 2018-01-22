@foreach(\App\Models\Notification::where('output_index', true)->notExpired()->orderBy('order')->orderByDesc('created_at')->orderBy('expired_at')->take(3)->get() as $notification)
    <div class="alert alert-{{ $notification->getBootstrapClass() }}">
        <span class="mr-1">{{ $notification->created_at->format('d.m.Y H:i') }}</span>
        &mdash;
        <span class="ml-1">{{ $notification->content }}</span>
    </div>
@endforeach