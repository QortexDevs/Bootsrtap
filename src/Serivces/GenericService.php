<?php

namespace Qortex\Bootstrap\Services;

abstract class GenericService
{
	protected $modelClassName = null;
	
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
	
}