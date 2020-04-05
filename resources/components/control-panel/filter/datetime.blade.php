<div class="form-group">
	{{ Form::label($key, $name, ['class' => 'control-label']) }}
	<div>
		{{ Form::date($key, $value, ['class' => 'form-control', $required ? 'required' : '']) }}
		@if ($required)
		<span class="help-block">Обязательное поле</span>
		@endif
	</div>
</div>