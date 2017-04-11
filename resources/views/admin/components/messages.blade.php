@if(\Session::has('success'))
    <div class="fixed-top">
        <div class="offset-md-2">
        @foreach(\Session::get('success') as $message)
            <div class="alert alert-info alert-dismissible fade show col-12 col-md-3 mx-auto my-2 text-center pl-5" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                {{ $message }}
            </div>
        @endforeach
        </div>
    </div>
@endif
