<?php

namespace Qortex\Bootstrap\Commands;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ModelMakeCommand extends \Illuminate\Foundation\Console\ModelMakeCommand
{
    protected $name = 'qortex:make:model';
    protected $description = 'Create a new Eloquent model class';

    private function checkModelsDirectory()
    {
        $modelsDirectory = app_path('Models');
        if (!file_exists($modelsDirectory)) {
            mkdir($modelsDirectory, 0755);
        }
    }

    protected function createService()
    {
        $service = Str::studly(class_basename($this->argument('name')));

        $modelName = $this->qualifyClass($this->getNameInput());

        $this->call('make:service', array_filter([
            'name'  => "{$service}Service",
            '--model' => $modelName
        ]));
    }

    public function handle()
    {
        $createModel = $this->option('model', false);
        $createMigration = $this->option('migration', false);
        $createService = $this->option('service', false);
        if (!$createModel && !$createMigration && !$createService) {
            $createModel = true;
            $createMigration = true;
            $createService = true;
        }
        if ($createModel) {
            $this->checkModelsDirectory();
            if (parent::handle() === false && !$this->option('force')) {
                return false;
            }
            if ($createMigration) {
                $createMigration = false;
                $this->input->setOption('migration', false);
            }
        }

        if ($createMigration) {
            $this->createMigration();
        }

        if ($createService) {
            $this->createService();
        }
    }

    protected function getOptions()
    {
        return [
            ['all', 'a', InputOption::VALUE_NONE, 'Generate a migration, seeder, factory, and resource controller for the model'],
            ['controller', 'c', InputOption::VALUE_NONE, 'Create a new controller for the model'],
            ['factory', 'f', InputOption::VALUE_NONE, 'Create a new factory for the model'],
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if the model already exists'],
            ['migration', 'm', InputOption::VALUE_NONE, 'Create a new migration file for the model'],
            ['model', 'mdl', InputOption::VALUE_NONE, 'Create a new model file for the model'],
            ['seed', 's', InputOption::VALUE_NONE, 'Create a new seeder file for the model'],
            ['service', 'srv', InputOption::VALUE_NONE, 'Create a new service file for the model'],
            ['pivot', 'p', InputOption::VALUE_NONE, 'Indicates if the generated model should be a custom intermediate table model'],
            ['resource', 'r', InputOption::VALUE_NONE, 'Indicates if the generated controller should be a resource controller'],
            ['api', null, InputOption::VALUE_NONE, 'Indicates if the generated controller should be an API controller'],
        ];
    }
}
