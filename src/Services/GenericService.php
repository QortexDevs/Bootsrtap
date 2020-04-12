<?php

namespace Qortex\Bootstrap\Services;

use Illuminate\Database\Eloquent\Model;

abstract class GenericService
{
	protected $modelClassName = null;

	protected $excludeProperties = [];

	private $defaultExcludeProperties = [
		'created_at', 
		'updated_at'
	];
	
	protected function getByFieldValueSingle($name, $value) {
		return $this->modelClassName::where($name, $value)->first();
	}
	
	protected function getByFieldValueMultiple($name, $value) {
		return $this->modelClassName::where($name, $value)->get();
	}
	
	protected function getByFieldValueIn($name, $values) {
		return $this->modelClassName::whereIn($name, $values)->get();
	}
	
	public function getAll() {
		return $this->modelClassName::all();
	}
	
	public function getById($id) {
		return $this->modelClassName::find($id);
	}
	
	public function getByIds($ids) {
		$keyFieldName = (new $this->modelClassName())->getKeyName();
		return $this->getByFieldValueIn($keyFieldName, $ids);
	}

	private function setProperties(Model $model, array $properties): void
	{
		$excludeProperties = array_merge($this->defaultExcludeProperties, $this->excludeProperties);
		foreach ($properties as $name => $value) {
			if (!in_array($name, $excludeProperties)) {
				$model->$name = $value;
			}
		}
	}

	public function create(array $properties): Model
	{
		$model = new $this->modelClassName;
		return $this->update($model, $properties);
	}

	public function update(Model $model, array $properties): Model
	{
		$this->setProperties($model, $properties);
		$model->save();
		return $model;
	}
	
}