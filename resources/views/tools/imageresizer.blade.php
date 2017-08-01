@extends('layouts.app')
@section('title')
    @lang('tools/imageresizer.header')
@endsection

@section('content')
    <div class="container mt-2">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <h4>@lang('tools/imageresizer.title')</h4>
                <form>
                    <div class="form-group mt-2">
                        <div class="text-center">
                            <label class="btn btn-primary d-inline-block align-top">@lang('tools/imageresizer.select_image')&hellip; <input type="file" style="display: none;" id="image"></label>
                            <a href="#" name="button" class="btn btn-success d-inline-block align-top" id="save">@lang('tools/imageresizer.save')</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="rectangleSlider">@lang('tools/imageresizer.move_slider')</label>
                        <input type="range" min="0" max="100" class="form-control mt-1" id="rectangleSlider" />
                    </div>
                </form>
            </div>
        </div>
        <br><br>
        <div class="row">
            <div class="col-md-12">
                <canvas id="imageCanvas" class="mx-auto img-responsive d-block"></canvas>
                <canvas id="hiddenCanvas" class="hidden-xs-up"></canvas>
            </div>
        </div>
    </div>
    <br><br><br>
@endsection

@section('scripts')
    <script>
        const displayWidth = {{ $imageResizeSettings['default']['displayWidth'] }};
        const displayHeight = {{ $imageResizeSettings['default']['displayHeight'] }};
        const outputWidth = {{ $imageResizeSettings['default']['outputWidth'] }};
        const outputHeight = {{ $imageResizeSettings['default']['outputHeight'] }};
        const HALF_SELECTION_RECTANGLE_SIZE = outputHeight / 2;
        const selectionRectangleColor = "{{ $imageResizeSettings['default']['selectionRectangleColor'] }}";
    </script>
    <script src="{{ mix('js/tools/imageresizer.js') }}"></script>
@endsection
