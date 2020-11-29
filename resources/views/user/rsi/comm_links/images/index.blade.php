@extends('user.layouts.default_wide')

@section('title', __('Comm-Link Bilder'))

@section('content')
    <h3>Comm-Link Bilder</h3>

    @if(is_callable([$images, 'links']) && method_exists($images, 'links'))
    <div>
        {{ $images->links() }}
    </div>
    @endif

    <div class="card-columns image-card-column">
        @foreach($images as $image)
            @include('user.rsi.comm_links.components.image_info_card', ['image' => $image])
        @endforeach
    </div>

    @if(is_callable([$images, 'links']) && method_exists($images, 'links'))
    <div>
        {{ $images->links() }}
    </div>
    @endif
    @include('user.components.upload_modal')
@endsection

@section('body__after')
    @parent
    <script>
        (() => {
            document.querySelectorAll('.badge.last-modified').forEach(entry => {
                entry.addEventListener('click', () => {
                    navigator.clipboard.writeText(entry.dataset.lastModified);
                });
            });
        })();
    </script>
@endsection