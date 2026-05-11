@props(['node', 'depth' => 0])
@use('App\Support\Game\DeepDiff')

@foreach($node as $key => $value)
    @if(DeepDiff::isLeaf($value))
        @php
            $oldNull = $value['old'] === null;
            $newNull = $value['new'] === null;
        @endphp

        <div class="flex items-start gap-2 py-0.5 {{ $depth > 0 ? 'pl-4 border-l border-base-300' : '' }}">
            <span class="font-mono text-xs text-subtle shrink-0 min-w-24">{{ $key }}:</span>
            @if($oldNull && ! $newNull)
                <span class="text-xs">{{ DeepDiff::formatValue($value['new']) }}</span>
            @elseif(! $oldNull && $newNull)
                <span class="text-xs line-through">{{ DeepDiff::formatValue($value['old']) }}</span>
            @elseif(! $oldNull && ! $newNull)
                <span class="text-xs">{{ DeepDiff::formatValue($value['old']) }}</span>
                <span class="text-xs text-subtle">→</span>
                <span class="text-xs">{{ DeepDiff::formatValue($value['new']) }}</span>
            @endif
        </div>
    @else
        @php
            $direction = DeepDiff::branchDirection($value);
        @endphp

        <div class="py-0.5 {{ $depth > 0 ? 'pl-4 border-l border-base-300' : '' }}">
            <div class="text-xs font-semibold py-0.5 {{ $direction === 'removed' ? 'line-through text-subtle' : '' }}">{{ $key }}</div>
            <div class="mt-0.5">
                @include('changelog.partials.change-tree', ['node' => $value, 'depth' => $depth + 1])
            </div>
        </div>
    @endif
@endforeach
