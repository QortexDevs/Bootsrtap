<?php

namespace Qortex\Bootstrap\Commands;

use Illuminate\Console\Command;

class GetEnvValueCommand extends Command
{
    protected $signature = 'get:env-value {envName}';
    protected $description = 'Получает значение перменной окружения текущего проекта';

    public function handle()
    {
        print env($this->argument('envName'), '');
        return 0;
    }
}
