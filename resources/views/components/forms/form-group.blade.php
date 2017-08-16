<div class="form-group {{ $class or '' }}">
    <label for="name" aria-label="Name">@lang('auth/account/edit.name'):</label>
    <input type="text" class="form-control" id="name" name="name" aria-labelledby="name" tabindex="1" value="{{ $user->name }}" autofocus>
</div>