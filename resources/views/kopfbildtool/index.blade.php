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
                        <label for="slider">Auswahl mit Slider oder Pfeiltasten bewegen</label>
                        <input type="range"  min="0" max="100" class="form-control mt-1" id="slider" onchange="change(this.value)" oninput="change(this.value)"/>
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
        var ready = false;
        var img = new Image();
        var imageLoader = document.getElementById('image');
        imageLoader.addEventListener('change', handleImage, false);

        var slider = document.getElementById('slider');

        var canvas = document.getElementById('imageCanvas');
        var ctx = canvas.getContext('2d');

        var canvasHidden = document.getElementById('hiddenCanvas');
        var ctxHidden = canvasHidden.getContext('2d');

        var rect_y = 0;
        var width = {!! $kopfbildSettings['default']['width'] !!} / 2;
        var aspect = 0;
        canvas.width = width;
        canvasHidden.width = {!! $kopfbildSettings['default']['width'] !!};

        function handleImage(event){
            var reader = new FileReader();
            reader.onload = function(event){
                img.onload = function(){
                    aspect = img.width / img.height;
                    canvas.height = (width / aspect);
                    slider.max = (canvas.height-125);
                    canvasHidden.height = ({!! $kopfbildSettings['default']['width'] !!} / aspect);
                    ctxHidden.drawImage(img, 0, 0, canvasHidden.width, canvasHidden.height);
                    ctx.drawImage(img, 0, 0, width, canvas.height);
                    drawRect(0, rect_y, {!! $kopfbildSettings['default']['outputwidth'] !!}, {!! $kopfbildSettings['default']['outputhight'] !!});
                    ready = true;
                }
                img.src = event.target.result;

                document.getElementById('save').onclick = function(){
                    crop(this, canvasHidden, 0, rect_y*2, {!! $kopfbildSettings['default']['width'] !!}, {!! $kopfbildSettings['default']['hight'] !!});
                };
            }
            reader.readAsDataURL(event.target.files[0]);
        }

        function drawRect(x,y,wid,hei) {
            ctx.strokeStyle = '#ff0000';
            ctx.strokeRect(x, y, wid, hei);
        }

        window.addEventListener("keydown", change);

        function change(e) {
            if (ready)
            {
                e = e || window.event;
                if (e.keyCode == '38') {
                    e.preventDefault();
                    if (rect_y > 0) {
                        rect_y--;
                    }
                }
                else if (e.keyCode == '40') {
                    e.preventDefault();
                    if (rect_y < canvas.height) {
                        rect_y++;
                    }
                }
                else {
                    rect_y = e;
                }
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.drawImage(img,0,0, width, canvas.height);
                drawRect(0, rect_y, {!! $kopfbildSettings['default']['outputwidth'] !!}, {!! $kopfbildSettings['default']['outputhight'] !!});
            }
        };

        var crop = function(link, canvasB, offsetX, offsetY, width, height) {
            var buffer = document.createElement('canvas');
            var b_ctx = buffer.getContext('2d');
            buffer.width = width;
            buffer.height = height;
            b_ctx.drawImage(canvasB, offsetX, offsetY, width, height, 0, 0, buffer.width, buffer.height);
            var image = buffer.toDataURL("image/jpeg");
            link.href = image;
            link.download = 'kopfbild.jpg';
            // window.location.href = image;
        };

    </script>
@endsection
