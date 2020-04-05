<div class="form-group">
	{{ Form::label($name, ($required ? '<span class="required">*</span> ' : '') . $label, ['class' => 'form-label col-md-4'], false) }}
	<div class="col-md-8 input-group pl-10 pr-10">
		<div class="input-group">
			{{ Form::text($name, $value, $attributes) }}
			<div class="input-group-btn">
				<button type="button"
					class="btn btn-info passy-generate-password-button">{{ $attributes['generate-password-button-caption'] }}</button>
			</div>
		</div>
		@if($errors->has($value))
		<p class="help-block help-block-error text-danger">{{ $errors->first($value) }}</p>
		@endif
	</div>
</div>