<div class="form-group">
	{{ Form::label($name, $label, ['class' => 'control-label']) }}
	<div>
		{{ Form::select($name . '[]', $values, $value, $attributes) }}
	</div>
</div>