@if (Session::has('messages') && count(Session::get('messages')) > 0)
    <div class="alert alert-default mb-4">
        <ul class="mb-0">
            @foreach(Session::get('messages') as $message)
                @if(is_array($message) && count($message) === 2)
                    <li class="text-{{$message[0]}}">{{ $message[1] }}</li>
                @else
                    <li>{{ $message }}</li>
                @endif
            @endforeach
        </ul>
    </div>
@endif