@extends('user.layouts.default_wide')

@section('title', __('Comm-Link Bilder'))

@section('content')
    <h3>@lang('Comm-Link Bilder') @if(isset($keyword)) @lang('zu') <code>{{ $keyword }}</code>@endif</h3>

    @if(is_callable([$images, 'links']) && method_exists($images, 'links'))
        <div class="d-flex justify-content-between">
            {{ $images->links() }}
            <form class="form-inline ml-auto" id="mimeForm">
                <label class="my-1 mr-2" for="mime">@lang('Mime Type')</label>
                <select class="custom-select form-control my-1 mr-sm-2" id="mime" multiple name="mime[]">
                    <option value="" @if(empty($selectedMimes)) selected @endif>@lang('Alle')</option>
                    @foreach($mimes as $mime)
                        <option value="{{ $mime->mime }}" @php if(in_array($mime->mime, $selectedMimes, true)) echo "selected"; @endphp>{{ $mime->mime }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    @endif
        <div class="image-card-column images-index mb-3">
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

            $('#mime').select2({
                closeOnSelect: false,
            });
            $('#mime').on('select2:close', function(e) {
                document.getElementById('mimeForm').submit();
            });

            document.querySelectorAll('.badge.last-modified').forEach(entry => {
                entry.addEventListener('click', () => {
                    navigator.clipboard.writeText(entry.dataset.lastModified)
                });
            });
        })();
    </script>
@endsection