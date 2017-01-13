@extends('layouts.app')
@section('title', 'Star Citizen Wiki Kopfbildtool')

@section('content')
    <div class="container mt-2">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <h4>Star Citizen Wiki Kopfbild Generator</h4>
                <form>
                    <div class="form-group mt-2">
                        <div class="text-center">
                            <label class="btn btn-primary d-inline-block align-top">Bild auswählen&hellip; <input type="file" style="display: none;" id="image"></label>
                            <a href="#" name="button" class="btn btn-success d-inline-block align-top" id="save">Speichern</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="rectangleSlider">Auswahlbereich per Slider bewegen</label>
                        <input type="range" min="0" max="100" class="form-control mt-1" id="rectangleSlider" onchange="moveSelectionRectangle(this.value)" oninput="moveSelectionRectangle(this.value)"/>
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
        window.addEventListener("keydown", moveSelectionRectangle);

        const displayWidth = {{ $kopfbildSettings['default']['displayWidth'] }};
        const displayHeight = {{ $kopfbildSettings['default']['displayHeight'] }};
        const outputWidth = {{ $kopfbildSettings['default']['outputWidth'] }};
        const outputHeight = {{ $kopfbildSettings['default']['outputHeight'] }};
        const HALF_SELECTION_RECTANGLE_SIZE = outputHeight / 2;
        const selectionRectangleColor = "{{ $kopfbildSettings['default']['selectionRectangleColor'] }}";

        var selectionRectangleOffset = 0;
        var aspectRatio = 0;
        var imageLoaded = false;


        var canvasImage = new Image();
        var imageLoader = document.getElementById('image');
            imageLoader.addEventListener('change', handleImage, false);

        var rectangleSlider = document.getElementById('rectangleSlider');

        var mainCanvas = document.getElementById('imageCanvas');
            mainCanvas.width = displayWidth;
        var mainCanvasContext = mainCanvas.getContext('2d');

        var hiddenFullSizeCanvas = document.getElementById('hiddenCanvas');
            hiddenFullSizeCanvas.width = outputWidth;
        var hiddenCanvasContext = hiddenFullSizeCanvas.getContext('2d');

        function handleImage(event) {
            var reader = new FileReader();
            reader.onload = function(event) {
                canvasImage.onload = function() {
                    aspectRatio = canvasImage.width / canvasImage.height;

                    mainCanvas.height = displayWidth / aspectRatio;
                    hiddenFullSizeCanvas.height = outputWidth / aspectRatio;

                    rectangleSlider.max = mainCanvas.height - HALF_SELECTION_RECTANGLE_SIZE;

                    mainCanvasContext.drawImage(canvasImage, 0, 0, displayWidth, mainCanvas.height);
                    hiddenCanvasContext.drawImage(canvasImage, 0, 0, outputWidth, hiddenFullSizeCanvas.height);

                    drawRectangle(0, selectionRectangleOffset, displayWidth, displayHeight);
                    imageLoaded = true;
                };
                canvasImage.src = event.target.result;

                document.getElementById('save').onclick = function() {
                    saveImageToDisk(hiddenFullSizeCanvas, this);
                };
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        function drawRectangle(offsetX, offsetY, rectangleWidth, rectangleHeight) {
            mainCanvasContext.strokeStyle = selectionRectangleColor;
            mainCanvasContext.strokeRect(offsetX, offsetY, rectangleWidth, rectangleHeight);
        }

        function moveSelectionRectangle(offsetY) {
            if (imageLoaded)
            {
                if (offsetY < 0) {
                    offsetY = 0;
                } else if (offsetY > mainCanvas.height - HALF_SELECTION_RECTANGLE_SIZE) {
                    offsetY = mainCanvas.height - HALF_SELECTION_RECTANGLE_SIZE;
                }
                selectionRectangleOffset = offsetY;
                mainCanvasContext.clearRect(0, 0, mainCanvas.width, mainCanvas.height);
                mainCanvasContext.drawImage(canvasImage,0,0, displayWidth, mainCanvas.height);
                drawRectangle(0, selectionRectangleOffset, displayWidth, displayHeight);
            }
        }

        function saveImageToDisk(sourceCanvas, windowContext) {
            var buffer = document.createElement('canvas');
            var bufferContext = buffer.getContext('2d');
            var offsetY = selectionRectangleOffset * 2;
            buffer.width = outputWidth;
            buffer.height = outputHeight;
            bufferContext.drawImage(sourceCanvas, 0, offsetY, outputWidth, outputHeight, 0, 0, outputWidth, outputHeight);
            windowContext.href = buffer.toDataURL("image/jpeg");
            windowContext.download = 'kopfbild.jpg';
        }
    </script>
@endsection
