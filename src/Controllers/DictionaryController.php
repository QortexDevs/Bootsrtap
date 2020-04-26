<?php

namespace Qortex\Bootstrap\Contollers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\Translation\Dumper\DumperInterface;

use App\Http\Controllers\Controller;

abstract class DictionaryContoller extends Controller
{
	protected $alias;
	protected $user;


	/** @return string */
	abstract protected function getModelClass(): string;

	/** @return string */
	abstract protected function getFormRequestClass(): string;

	protected function getCurrentUser()
	{
		if (!$this->user) {
			$this->user = auth()->user();
		}
		return $this->user;
	}

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
		View::share('routeAlias', $this->alias);
	}


	/**
	 * @param $id integer
	 * @return \Illuminate\Database\Eloquent\Model
	 */
	protected function initModel($id = null): \Illuminate\Database\Eloquent\Model
	{
		$className = $this->getModelClass();
		$model = (!$id) ? new $className() : call_user_func([$className, 'findOrFail'], $id);
		return $model;
	}

	/** @return \Illuminate\Foundation\Http\FormRequest */
	protected function getFormRequest(): \Illuminate\Foundation\Http\FormRequest
	{
		return app($this->getFormRequestClass());
	}

	/**
	 * @param $model \Illuminate\Database\Eloquent\Model
	 * @param $isNewModel bool
	 * @return mixed
	 */
	protected function afterSaveModel($model, $isNewModel, $request)
	{
		return true;
	}

	protected function currentUserCanListModels($model)
	{
		return $this->getCurrentUser()->can('list', new $model);
	}

	protected function currentUserCanCreateModel($model)
	{
		return !$model->exists && $this->getCurrentUser()->can('create', $model);
	}

	protected function currentUserCanViewModel($model)
	{
		return $model->exists && $this->getCurrentUser()->can('view', $model);
	}

	protected function currentUserCanUpdateModel($model)
	{
		return $model->exists && $this->getCurrentUser()->can('update', $model);
	}

	protected function currentUserCanDeleteModel($model)
	{
		return !$model->exists && $this->getCurrentUser()->can('delete', $model);
	}

	public function list()
	{
		$modelClassName = $this->getModelClass();
		if ($this->currentUserCanListModels($modelClassName)) {
			return view($this->alias . '.list', compact('modelClassName'));
		}
		return view('errors.permission-denied');
	}

	public function form($id = null)
	{
		$model = $this->initModel($id);
		if ($model->exists && $this->currentUserCanUpdateModel($model)) {
			return view($this->alias . '.form', compact('model'));
		}
		if (!$model->exists && $this->currentUserCanCreateModel($model)) {
			return view($this->alias . '.form', compact('model'));
		}
		return view('errors.permission-denied');
	}

	public function save($id = null)
	{
		$model = $this->initModel($id);
		if (!($this->currentUserCanCreateModel($model) || $this->currentUserCanUpdateModel($model))) {
			return view($this->alias . '.form', compact('model'));
		}

		$request = $this->getFormRequest();
		$formData = $request->validated();

		foreach ($formData as $attributeName => $value) {
			$model->{$attributeName} = $value;
		}

		$model->save();
		$this->afterSaveModel($model, $model->exists, $request);
		return redirect()->route($this->alias . '.edit', $model->id);
	}


	public function delete($id)
	{
		$model = $this->initModel($id);
		if (!$this->currentUserCanDeleteModel($model)) {
			$model->delete();
		}
		return redirect()->route($this->alias . '.list');
	}

	public function show($id)
	{
		$model = $this->initModel($id);
		if (!$model->exists && $this->currentUserCanViewModel($model)) {
			return view($this->alias . '.form', compact('model'));
		}
		return view('errors.permission-denied');
	}
}
