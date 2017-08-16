<nav class="navbar navbar-expand-lg navbar-dark {{ $class or '' }}">{{--
    --}}<?php if (isset($title) && !empty($title)) { ?>{{--
        --}}<a class="navbar-brand" href="{{ $titleLink or '#' }}">{{ $title }}</a>{{--
    --}}<?php } ?>{{--
    --}}<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#nav-top-menu" aria-controls="nav-top-menu" aria-expanded="false" aria-label="Toggle navigation">{{--
        --}}<span class="navbar-toggler-icon"></span>{{--
    --}}</button>{{--
    --}}<div class="collapse navbar-collapse justify-content-end {{ $contentClass or '' }}" id="nav-top-menu">{{--
        --}}<ul class="navbar-nav">{{--
            --}}{{ $slot or '' }}{{--
        --}}</ul>{{--
    --}}</div>
</nav>