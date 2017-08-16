<section class="nav flex-column mb-4 {{ $class or '' }}" {{ $options or '' }}>{{--
--}}<li class="nav-item">
        @if(isset($isLink) && !empty($isLink)){{--
        --}}<a class="nav-link {{ $titleClass or '' }}" data-toggle="collapse" href="#{{ $id or '' }}" aria-expanded="false" aria-controls="{{ $id or '' }}">{{--
            --}}{{ $title or '' }} <i class="fa fa-caret-down ml-2"></i>{{--
        --}}</a>
        @else
            @unless(empty($title))
                <span class="nav-link {{ $titleClass or '' }}">{{--
                    --}}{{ $title or '' }}{{--
            --}}</span>
            @endunless
        @endif{{--
    --}}<ul class="flex-column list-unstyled @if(isset($isLink) && !empty($isLink)) collapse @endif {{ $contentClass or '' }}" id="{{ $id or '' }}">{{--
        --}}{{ $slot }}{{--
    --}}</ul>{{--
--}}</li>
</section>
