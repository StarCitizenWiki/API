@extends('user.layouts.default_wide')

@section('title', __('Comm-Link Bilder'))

@section('content')
    <h3>Comm-Link Bilder @if(isset($keyword)) zu <code>{{ $keyword }}</code>@endif</h3>

    @if(is_callable([$images, 'links']) && method_exists($images, 'links'))
        <div class="d-flex justify-content-between">
            {{ $images->links() }}
            <form class="form-inline ml-auto" id="mimeForm">
                <label class="my-1 mr-2" for="mime">Mime Type</label>
                <select class="custom-select my-1 mr-sm-2" id="mime">
                    <option value="" selected>Alle</option>
                    @foreach($mimes as $mime)
                        <option value="{{ $mime->mime }}">{{ $mime->mime }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif

    <div class="card-columns image-card-column images-index">
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
        const hoverVideoPlay = () => {
            document.querySelectorAll('video').forEach(video => {
                video.addEventListener('mouseenter', () => {
                    video.play();
                });

                video.addEventListener('mouseleave', () => {
                    video.pause();
                });
            })
        }

        (() => {
            hoverVideoPlay();

            document.querySelectorAll('.badge.last-modified').forEach(entry => {
                entry.addEventListener('click', () => {
                    navigator.clipboard.writeText(entry.dataset.lastModified)
                });
            });

            const currentUrl = new URL(window.location)
            const mimeSelect = document.getElementById('mime')

            if (currentUrl.searchParams.has('mime')) {
                mimeSelect.querySelectorAll('option').forEach(option => {
                    option.selected = option.value === currentUrl.searchParams.get('mime');
                })

                document.querySelectorAll('nav .page-item a.page-link').forEach(navLink => {
                    const url = new URL(navLink.href)
                    url.searchParams.append('mime', currentUrl.searchParams.get('mime'))

                    navLink.href = url.toString()
                })
            }

            mimeSelect.addEventListener('change', (ev) => {
                currentUrl.searchParams.delete('page')
                currentUrl.searchParams.delete('mime')
                currentUrl.searchParams.append('mime', ev.target.value)

                window.location = currentUrl.href
            })
        })();
    </script>
@endsection