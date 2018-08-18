<div class="card {{ $class or '' }}" {{ $options or '' }}>
    @if(isset($title))
        @if(starts_with($title, '#'))
            <h4 class="card-header {{ $titleClass or '' }}">
                @if(isset($icon))
                    @component('components.elements.icon', [
                        'class' => 'mr-1',
                    ])
                        @slot('options')
                            data-fa-transform="up-2"
                        @endslot
                        {{ $icon }}
                    @endcomponent
                @endif
                {{ ltrim($title, '#') }}
            </h4>
        @else
            <div class="card-header {{ $titleClass or '' }}">
                @if(isset($icon))
                    @component('components.elements.icon', [
                        'class' => 'mr-1'
                    ])
                        {{ $icon }}
                    @endcomponent
                @endif
                {{ $title }}
            </div>
        @endif
    @endif
    <div class="card-body {{ $contentClass or '' }}">
        {{ $slot }}
    </div>
    @if(isset($footer))
        <div class="card-footer {{ $footerClass or '' }}">
            {{ $footer }}
        </div>
    @endif
</div>
