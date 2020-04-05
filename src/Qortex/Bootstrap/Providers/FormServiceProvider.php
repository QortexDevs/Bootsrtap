<?php

namespace Qortex\Bootstrap\Providers;

use Illuminate\Support\ServiceProvider;

use Form;
use Carbon\Carbon;

class FormServiceProvider extends ServiceProvider
{
	public function mergeAttributes($defaultAttributes, $userAttributes)
	{
		foreach ($defaultAttributes as $attributeName => $attrubuteValue) {
			if (array_key_exists($attributeName, $userAttributes)) {
				$userAttributes[$attributeName] .= ' ' . $attrubuteValue;
			} else {
				$userAttributes[$attributeName] = $attrubuteValue;
			}
		}
		return $userAttributes;
	}
	/**
	 * Register bindings in the container.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$self = $this;
		Form::component('datetimeFilterControl', 'qortex.components.control-panel.filter.datetime', [
			'name',
			'key',
			'value' => null,
			'defaultValue' => null,
			'required' => false,
			'attributes' => []
		]);
		Form::macro('cpFilterDatetime', function ($name, $key, $value = null, $defaultValue = null, $required = false, $attrubutes = []) {
			if (!$value) {
				$value = $defaultValue;
			}
			if (!$value) {
				$value = Carbon::now();
			}
			$value = $value->format('Y-m-d');

			return Form::datetimeFilterControl($name, $key, $value, $required, $attrubutes);
		});

		Form::component('selectMultipleFilterControl', 'qortex.components.control-panel.filter.select-multiple', [
			'label',
			'name',
			'values' => [],
			'value' => null,
			'attributes' => []
		]);
		Form::macro('cpFilterSelectMultiple', function ($label, $name, $values = [], $value = null, $attributes = []) use ($self) {
			if (isset($attributes['selectall'])) {
				array_unshift($values, ['id' => '0', 'name' => 'Все']);
				unset($attributes['selectall']);
			}
			$values = collect($values)->pluck('name', 'id');
			$defaultAttributes = [
				'class' => 'form-control select',
				'multiple' => 'multiple',
			];
			$attributes = $self->mergeAttributes($defaultAttributes, $attributes);
			return Form::selectMultipleFilterControl($label, $name, $values, $value, $attributes);
		});

		Form::component('selectFilterControl', 'qortex.components.control-panel.filter.select', [
			'label',
			'name',
			'values' => [],
			'value' => null,
			'attributes' => []
		]);
		Form::macro('cpFilterSelect', function ($label, $name, $values = [], $value = null, $attributes = []) use ($self) {
			if (isset($attributes['selectall'])) {
				array_unshift($values, ['id' => '0', 'name' => 'Все']);
				unset($attributes['selectall']);
			}
			$values = collect($values)->pluck('name', 'id');
			$defaultAttributes = [
				'class' => 'form-control select'
			];
			$attributes = $self->mergeAttributes($defaultAttributes, $attributes);
			return Form::selectFilterControl($label, $name, $values, $value, $attributes);
		});

		Form::component('datetimeFormControl', 'qortex.components.control-panel.form.datetime', [
			'name',
			'key',
			'value' => null,
			'defaultValue' => null,
			'required' => false,
			'attributes' => []
		]);
		Form::macro('cpFormDatetime', function ($name, $key, $value = null, $defaultValue = null, $required = false, $attrubutes = []) {
			if (!$value) {
				$value = $defaultValue;
			}
			if (!$value) {
				$value = Carbon::now();
			}
			$value = $value->format('Y-m-d');

			return Form::datetimeFilterControl($name, $key, $value, $defaultValue, $required, $attrubutes);
		});

		Form::component('selectMultipleFormControl', 'qortex.components.control-panel.form.select-multiple', [
			'name',
			'label',
			'values' => [],
			'value' => null,
			'required' => false,
			'attributes' => []
		]);
		Form::macro('cpFormSelectMultiple', function ($name, $label, $values = [], $value = null, $required = false, $attrubutes = []) {
			$values = collect($values)->pluck('name', 'id');
			return Form::selectMultipleFormControl($name, $label, $values, $value, $required, $attrubutes);
		});

		Form::component('selectFormControl', 'qortex.components.control-panel.form.select', [
			'name',
			'label',
			'values' => [],
			'value' => null,
			'required' => false,
			'attributes' => []
		]);
		Form::macro('cpFormSelect', function ($name, $label, $values = [], $value = null, $required = false, $attributes = []) use ($self) {
			$values = collect($values)->pluck('name', 'id');
			if (in_array('add-none', $attributes)) {
				$values->prepend('Выберите значение', '');
			}

			if (array_key_exists('add-none', $attributes)) {

				$values->prepend($attributes['add-none'], '');
			}
			$defaultAttributes = [
				'data-minimum-results-for-search' => 3,
				'class' => 'form-control select2'
			];
			$attributes = $self->mergeAttributes($defaultAttributes, $attributes);
			return Form::selectFormControl($name, $label, $values, $value, $required, $attributes);
		});

		Form::component('textFormControl', 'qortex.components.control-panel.form.text', [
			'name',
			'label',
			'value' => null,
			'required' => false,
			'attributes' => []
		]);
		Form::macro('cpFormText', function ($name, $label, $value = null, $required = false, $attributes = []) use ($self) {
			$defaultAttributes = [
				'class' => 'form-control'
			];
			$attributes = $self->mergeAttributes($defaultAttributes, $attributes);
			return Form::textFormControl($name, $label, $value, $required, $attributes);
		});

		Form::component('emailFormControl', 'qortex.components.control-panel.form.email', [
			'name',
			'label',
			'value' => null,
			'required' => false,
			'attributes' => []
		]);
		Form::macro('cpFormEmail', function ($name, $label, $value = null, $required = false, $attributes = []) use ($self) {
			$defaultAttributes = [
				'class' => 'form-control'
			];
			$attributes = $self->mergeAttributes($defaultAttributes, $attributes);
			return Form::emailFormControl($name, $label, $value, $required, $attributes);
		});

		Form::component('phoneFormControl', 'qortex.components.control-panel.form.phone', [
			'name',
			'label',
			'value' => null,
			'required' => false,
			'attributes' => []
		]);
		Form::macro('cpFormPhone', function ($name, $label, $value = null, $required = false, $attributes = []) use ($self) {
			$defaultAttributes = [
				'class' => 'form-control'
			];
			$attributes = $self->mergeAttributes($defaultAttributes, $attributes);
			return Form::phoneFormControl($name, $label, $value, $required, $attributes);
		});


		Form::component('passwordFormControl', 'qortex.components.control-panel.form.password', [
			'name',
			'label',
			'value' => null,
			'required' => false,
			'attributes' => []
		]);
		Form::macro('cpFormPassword', function ($name, $label, $value = null, $required = false, $attributes = []) use ($self) {
			$defaultAttributes = [
				'class' => 'form-control passy-password-input',
				'generate-password-button-caption' => 'Сгенерировать',
			];
			$attributes = $self->mergeAttributes($defaultAttributes, $attributes);
			return Form::passwordFormControl($name, $label, $value, $required, $attributes);
		});

		Form::component('buttonFormControl', 'qortex.components.control-panel.form.button', [
			'name',
			'label',
			'value' => null,
			'attributes' => []
		]);
		Form::macro('cpFormButton', function ($name, $label, $value = null, $attributes = []) use ($self) {
			$defaultAttributes = [
				'class' => implode(' ', ['btn', 'btn-sm', 'bg-violet-800', $value])
			];
			$attributes = $self->mergeAttributes($defaultAttributes, $attributes);
			return Form::buttonFormControl($name, $label, $value, $attributes);
		});

		Form::macro('cpFormDangerButton', function ($name, $label, $value = null, $attributes = []) use ($self) {
			$defaultAttributes = [
				'class' => implode(' ', ['btn', 'btn-sm', 'btn-danger', $value])
			];
			$attributes = $self->mergeAttributes($defaultAttributes, $attributes);
			return Form::buttonFormControl($name, $label, $value, $attributes);
		});

		Form::component('textareaFormControl', 'qortex.components.control-panel.form.textarea', [
			'name',
			'label',
			'value' => null,
			'required' => false,
			'attributes' => []
		]);
		Form::macro('cpFormTextarea', function (string $name, string $label, $value = null, array $attributes = []) use ($self) {
			$defaultAttributes = [
				'class' => 'form-control',
			];
			$attributes = $self->mergeAttributes($defaultAttributes, $attributes);
			$required = in_array('required', $attributes);
			return Form::textareaFormControl($name, $label, $value, $required, $attributes);
		});

		Form::component('infoFormControl', 'qortex.components.control-panel.form.info', [
			'label',
			'value' => null,
			'attributes' => []
		]);
		Form::macro('cpFormInfo', function ($label, $value = null, $attrubutes = []) {
			return Form::infoFormControl($label, $value, $attrubutes);
		});
	}
}
