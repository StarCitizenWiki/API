<div class="card mb-3 rounded" style="overflow: hidden" title="{{ $title or '' }}">
    <div class="card">
        <div class="card-header text-left text-white bg-dark">
            <i class="fa fa-{{ $icon or '' }} mr-1"></i>
            {{ $slot or '' }}
        </div>
        <div class="card-block bg-white text-center">
            <h3 class="card-title mb-0">{{ $content or '' }}</h3>
            <small class="card-text">{{ $text or '' }}</small>
        </div>
    </div>
</div>
