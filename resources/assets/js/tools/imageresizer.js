/**
 * Created by Hanne on 05.02.2017.
 */
var selectionRectangleOffset = 0;
var aspectRatio = 0;
var imageLoaded = false;

var canvasImage = new Image();
var imageLoader = document.getElementById('image');
imageLoader.addEventListener('change', loadImageToCanvas, false);

var rectangleSlider = document.getElementById('rectangleSlider');
rectangleSlider.addEventListener('input', function () {
    moveSelectionRectangle(rectangleSlider.value);
}, false);

var mainCanvas = document.getElementById('imageCanvas');
mainCanvas.width = displayWidth;
var mainCanvasContext = mainCanvas.getContext('2d');

var hiddenFullSizeCanvas = document.getElementById('hiddenCanvas');
hiddenFullSizeCanvas.width = outputWidth;
var hiddenCanvasContext = hiddenFullSizeCanvas.getContext('2d');

var saveButton = document.getElementById('save');
saveButton.onclick = function() {
    saveImageToDisk(hiddenFullSizeCanvas, this);
};

function loadImageToCanvas(event) {
    var reader = new FileReader();
    reader.onload = function(event) {
        canvasImage.onload = function() {
            setCanvasAttributes();
            drawImages();
            drawSelectionRectangle(0, selectionRectangleOffset, displayWidth, displayHeight);
            imageLoaded = true;
        };
        canvasImage.src = event.target.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}

function setCanvasAttributes() {
    aspectRatio = canvasImage.width / canvasImage.height;

    mainCanvas.height = displayWidth / aspectRatio;
    hiddenFullSizeCanvas.height = outputWidth / aspectRatio;

    rectangleSlider.max = mainCanvas.height - HALF_SELECTION_RECTANGLE_SIZE;
}

function drawImages() {
    mainCanvasContext.drawImage(canvasImage, 0, 0, displayWidth, mainCanvas.height);
    hiddenCanvasContext.drawImage(canvasImage, 0, 0, outputWidth, hiddenFullSizeCanvas.height);
}

function drawSelectionRectangle(offsetX, offsetY, rectangleWidth, rectangleHeight) {
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
        drawSelectionRectangle(0, selectionRectangleOffset, displayWidth, displayHeight);
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