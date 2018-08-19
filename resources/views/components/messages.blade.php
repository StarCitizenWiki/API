@if (Session::has('messages') && count(Session::get('messages')) > 0)
    @foreach(Session::get('messages') as $type => $messages)
        @if (is_string($type))
            <div class="alert alert-{{ $type }} mb-4">
                <ul class="mb-0">
                    @foreach($messages as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @else
            <div class="alert alert-default mb-4">
                <ul class="mb-0">
                    <li>{{ $messages }}</li>
                </ul>
            </div>
        @endif
    @endforeach
@endif