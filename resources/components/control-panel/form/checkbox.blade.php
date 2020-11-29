<div class="form-check row">
    <label for="{{ $name }}" class="form-check-label">
        {{ Form::checkbox($name, 1, (bool)$checked, $attributes) }}
        {{ $label }}
    </label>
    @if($errors->has($name))
    <p class="help-block help-block-error text-danger">{{ $errors->first($name) }}</p>
    @endif
</div>