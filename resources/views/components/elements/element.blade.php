<{{ $type }} id="{{ $id or '' }}" class="{{ $class or '' }}" {{ $options or '' }}
@unless(in_array($type, ['img', 'input']))
>{{ $slot or '' }}</{{ $type }}>
@else
/>
@endunless