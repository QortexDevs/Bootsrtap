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

    protected function getByFieldValueSingle($name, $value)
    {
        return $this->modelClassName::where($name, $value)->first();
    }

    protected function getByFieldValueMultiple($name, $value)
    {
        return $this->modelClassName::where($name, $value)->get();
    }

    protected function getByFieldValueIn($name, $values)
    {
        return $this->modelClassName::whereIn($name, $values)->get();
    }

    public function getAll()
    {
        return $this->modelClassName::all();
    }

    public function getById($id)
    {
        return $this->modelClassName::find($id);
    }

    public function getByIds($ids)
    {
        $keyFieldName = (new $this->modelClassName())->getKeyName();
        return $this->getByFieldValueIn($keyFieldName, $ids);
    }

    protected function setProperties(Model $model, array $properties): Model
    {
        $excludeProperties = array_merge($this->defaultExcludeProperties, $this->excludeProperties);
        foreach ($properties as $name => $value) {
            if (!in_array($name, $excludeProperties)) {
                $model->$name = $value;
            }
        }
        return $model;
    }

    public function create(array $properties): Model
    {
        $model = new $this->modelClassName;
        return $this->update($model, $properties);
    }

    public function update(Model $model, array $properties): Model
    {
        $syncMethods = [];
        foreach ($properties as $name => $value) {
            if (method_exists($model, $name)) {
                $syncMethods[$name] = $value;
                unset($properties[$name]);
            }
        }
        $this->setProperties($model, $properties);
        $model->save();
        foreach ($syncMethods as $name => $value) {
            $model->$name()->sync($value);
        }
        return $model;
    }

    public function destroy(Model $model)
    {
        $model->forceDelete();
    }
}
