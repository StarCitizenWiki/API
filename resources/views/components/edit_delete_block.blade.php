<div class="btn-group btn-group-sm" role="group" aria-label="">
    @unless(empty($edit_url))
    <a href="{{ $edit_url or '' }}" class="btn btn-outline-secondary">
        <i class="fa fa-pencil"></i>
    </a>
    @endunless
    @unless(empty($delete_url))
    <a href="#" class="btn btn-outline-danger" onclick="event.preventDefault(); document.getElementById('delete-form{{ $slot }}').submit();">
        <form id="delete-form{{ $slot }}" action="{{ $delete_url or '' }}" method="POST" style="display: none;">
            <input name="_method" type="hidden" value="DELETE">
            <input name="id" type="hidden" value="{{ $slot }}">
            {{ csrf_field() }}
        </form>
        <i class="fa fa-trash-o"></i>
    </a>
    @endunless
</div>