<div class="card {{ $class ?? '' }}" {{ $options ?? '' }}>
    @if(isset($title))
        @if(\Illuminate\Support\Str::startsWith($title, '#'))
            <h4 class="card-header {{ $titleClass ?? '' }}">
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
            <div class="card-header {{ $titleClass ?? '' }}">
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
    <div class="card-body {{ $contentClass ?? '' }}">
        {{ $slot }}
    </div>
    @if(isset($footer))
        <div class="card-footer {{ $footerClass ?? '' }}">
            {{ $footer }}
        </div>
    @endif
</div>
