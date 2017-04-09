<div class="card mb-3 rounded" style="overflow: hidden" title="{{ $title or '' }}">
    <div class="card-block p-0 clearfix">
        <i class="fa fa-{{ $icon or '' }} bg-inverse py-4 text-white mr-1 float-left display-5 col-5 text-center"></i>
        <div class="h5 mb-0 pt-4 text-center">{{ $content or '' }}</div>
        <div class="text-muted text-uppercase font-weight-bold font-xs text-center">{{ $slot or '' }}</div>
    </div>
</div>