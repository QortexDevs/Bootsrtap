<div class="form-group row">
	{{ Form::label($name, ($required ? '<span class="required">*</span> ' : '') . $label, ['class' => 'col-form-label col-md-4']) }}
	<div class="col-md-8">
		{{ Form::select($name . '[]', $values, $value, ['class' => 'form-control select', 'multiple']) }}
		@if($errors->has($name))
            <p class="help-block help-block-error text-danger">{{ $errors->first($name) }}</p>
        @endif
	</div>
</div>