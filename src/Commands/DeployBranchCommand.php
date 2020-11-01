<?php

namespace Qortex\Bootstrap\Commands;

use Illuminate\Console\Command;

class DeployBranchCommand extends Command
{
    protected $signature = 'deploy {--full}';
    protected $description = 'Деплоит текущую ветку как отдельный поддомен';

    private function getCurrentBranchName()
    {
        //exec('git branch --show-current', $output, $status);
        exec('git rev-parse --abbrev-ref HEAD', $output, $status);
        if ($status === 0) {
            if (count($output) > 0) {
                return $output[0];
            }
        }
        return null;
    }

    private function printStatus()
    {
        exec('git status', $output, $status);
        if ($status === 0) {
            foreach ($output as $line) {
                $this->line($line);
            }
        }
    }

    private function isWorkingDirectoryIsClean()
    {
        exec('git status --porcelain', $output, $status);
        if ($status === 0) {
            if (count($output) > 0) {
                $this->printStatus();
                return false;
            }
        }
        return true;
    }

    private function commit($message)
    {
        exec('git add .', $output, $status);
        exec('git commit -a -m "' . $message . '"', $output, $status);
    }

    private function pushBranchToRemote($branchName)
    {
        exec('git push -f --set-upstream origin ' . $branchName, $output, $status);
        if ($status === 0) {
            if (count($output) > 0) {
                return $output[0];
            }
        }
        return null;
    }

    private function cloneRepositoryOnStagingServer($repositoryName, $branchName, $userName, $domain)
    {
        $stubFile = '.env.deploy-per-branch.stub';
        $envFile = '.env';
        $commands = [
            'cd ~/branches/',
            'rm -fR {{branchName}}.{{domain}}',
            'mkdir {{branchName}}.{{domain}}',
            'cd {{branchName}}.{{domain}}',
            'rm -fR www',
            'git clone -b {{branchName}} --single-branch {{repositoryName}} www',
            'cd www',
            'composer install',
            'npm install',
            'npm run dev',
            'cp ' . $stubFile . ' ' . $envFile,
            'sed -i \"s/{{ applicationHost }}/{{branchName}}.{{domain}}/\" ' . $envFile,
            'sed -i \"s/{{ branchName }}/{{branchName}}/\" ' . $envFile,
            'php artisan migrate',
        ];
        $arguments = array_map(function ($item) use ($repositoryName, $branchName, $userName, $domain) {
            return str_replace(
                [
                    '{{repositoryName}}',
                    '{{branchName}}',
                    '{{userName}}',
                    '{{domain}}'
                ],
                [
                    $repositoryName,
                    $branchName,
                    $userName,
                    $domain
                ],
                $item
            );
        }, $commands);

        $command = 'ssh -o StrictHostKeyChecking=no ' . $userName . '@' . $branchName . '.' . $domain . ' "' . implode(' && ', $arguments) . '"';
        exec($command, $output, $status);
    }

    private function pullBranchOnStagingServer($repositoryName, $branchName, $userName, $domain)
    {
        $commands = [
            'cd ~/branches/{{branchName}}.{{domain}}/www',
            'git pull',
            'npm run dev',
            'php artisan migrate',
        ];
        $arguments = array_map(function ($item) use ($repositoryName, $branchName, $userName, $domain) {
            return str_replace(
                [
                    '{{repositoryName}}',
                    '{{branchName}}',
                    '{{userName}}',
                    '{{domain}}'
                ],
                [
                    $repositoryName,
                    $branchName,
                    $userName,
                    $domain
                ],
                $item
            );
        }, $commands);

        $command = 'ssh -o StrictHostKeyChecking=no ' . $userName . '@' . $branchName . '.' . $domain . ' "' . implode(' && ', $arguments) . '"';
        exec($command, $output, $status);
    }

    private function restoreDatabase($branchName, $userName, $domain)
    {
        $mysqlRootUser = env('MYSQL_ROOT_USER');
        $mysqlRootPassword = env('MYSQL_ROOT_PASSWORD');
        $commands = [
            'cd ~/branches/{{branchName}}.{{domain}}/www',
            'mysql -u{{mysqlRootUser}} -p{{mysqlRootPassword}} -e \"CREATE USER IF NOT EXISTS \'{{branchName}}\'@\'localhost\' IDENTIFIED BY \'stage_password\';\"',
            'mysql -u{{mysqlRootUser}} -p{{mysqlRootPassword}} -e \"CREATE DATABASE IF NOT EXISTS {{branchName}};\"',
            'aws s3 cp s3://delo-backup/db-backup/mysql-full/latest.sql ./{{branchName}}.sql',
            'mysql -u{{branchName}} -pstage_password --database={{branchName}} < ./{{branchName}}.sql',
            'rm ./{{branchName}}.sql',
            'npm run dev',
            'php artisan migrate',
        ];
        $arguments = array_map(function ($item) use ($mysqlRootUser, $mysqlRootPassword, $branchName, $userName, $domain) {
            return str_replace(
                [
                    '{{mysqlRootUser}}',
                    '{{mysqlRootPassword}}',
                    '{{branchName}}',
                    '{{userName}}',
                    '{{domain}}'
                ],
                [
                    $mysqlRootUser,
                    $mysqlRootPassword,
                    $branchName,
                    $userName,
                    $domain,
                ],
                $item
            );
        }, $commands);

        $command = 'ssh -o StrictHostKeyChecking=no ' . $userName . '@' . $branchName . '.' . $domain . ' "' . implode(' && ', $arguments) . '"';
        exec($command, $output, $status);
    }

    public function handle()
    {
        $full = $this->option('full');

        $deployDomain = env('DEPLOY_DOMAIN');
        $deployUser = env('DEPLOY_USER');
        $deployRepository = env('DEPLOY_REPOSITORY');

        $currentBranchName = $this->getCurrentBranchName();
        $this->line('Начинаю деплой ветки «' . $currentBranchName . '»');
        $workingDirectoryClean = $this->isWorkingDirectoryIsClean();
        if (!$workingDirectoryClean) {
            $message = '';
            while ($message == '') {
                $message = $this->ask('Перед деплоем нужно закоммитить изменения. Введите сообщение для коммита или нажмите Ctrl+C, чтобы прервать операцию. Сообщение для коммита');
            }
            $this->commit($message);
            $this->pushBranchToRemote($currentBranchName);
        }
        if ($full) {
            $this->cloneRepositoryOnStagingServer($deployRepository, $currentBranchName, $deployUser, $deployDomain);
            $this->line('Загружаю последнюю версию базы данных для «' . $currentBranchName . '»');
            $this->restoreDatabase($currentBranchName, $deployUser, $deployDomain);
        } else {
            $this->pullBranchOnStagingServer($deployRepository, $currentBranchName, $deployUser, $deployDomain);
        }
        $this->line('Всё готово!');
        $this->line('Ветка доступна по адресу: https://' . $currentBranchName . '.' . $deployDomain);
    }
}
