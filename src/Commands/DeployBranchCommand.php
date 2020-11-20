<?php

namespace Qortex\Bootstrap\Commands;

class DeployBranchCommand extends GenericShellExecuteCommand
{
    use Traits\ManagesRemotePorts;

    protected $signature = 'deploy {--full}';
    protected $description = 'Деплоит текущую ветку как отдельный поддомен';

    private function getCurrentBranchName()
    {
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

    private function stopFrontend(string $hostName, string $userName, array $arguments)
    {
        $currentCommentsPort = $this->getRemoveEnvValue($hostName, $userName, 'NODE_SERVER_COMMENTS_PORT');
        $currentCollaborateEditorPort = $this->getRemoveEnvValue($hostName, $userName, 'NODE_SERVER_COLLABORATE_EDITOR_PORT');
        if ($currentCommentsPort) {
            $this->stopServerByPort($hostName, $userName, $currentCommentsPort);
        }
        if ($currentCollaborateEditorPort) {
            $this->stopServerByPort($hostName, $userName, $currentCollaborateEditorPort);
        }
    }

    private function clearExistingBranchFiles(string $hostName, string $userName, array $arguments)
    {
        $commands = [
            'cd ~/branches/',
            'rm -fR {{hostName}}',
        ];
        $this->executeRemoteCommands($hostName, $userName, $commands, null, $arguments);
    }

    private function clearExistingBranch(string $hostName, string $userName, array $arguments)
    {
        $this->stopFrontend($hostName, $userName, $arguments);
        $this->clearExistingBranchFiles($hostName, $userName, $arguments);
    }

    private function cloneBranchOnStagingServer(string $hostName, string $userName, array $arguments)
    {
        $commands = [
            'cd ~/branches/{{hostName}}/',
            'git clone -b {{branchName}} --single-branch {{repositoryName}} www',
        ];
        $this->executeRemoteCommands($hostName, $userName, $commands, null, $arguments);
    }

    private function prepareEnvFile($hostName, $userName, $arguments)
    {
        $commands = [
            'cd ~/branches/{{hostName}}/www',
            'cp {{stubFile}} {{envFile}}',
            'sed -i \"s/{{ APPLICATION_HOST }}/{{hostName}}/\" {{envFile}}',
            'sed -i \"s/{{ DB_DATABASE }}/{{branchName}}/\" {{envFile}}',
            'sed -i \"s/{{ DB_USERNAME }}/{{branchName}}/\" {{envFile}}',
            'sed -i \"s/{{ NODE_SERVER_COMMENTS_HOST }}/{{commentsPort}}/\" {{envFile}}',
            'sed -i \"s/{{ NODE_SERVER_COLLABORATE_EDITOR_PORT }}/{{collaborateEditorPort}}/\" {{envFile}}',
        ];
        $this->executeRemoteCommands($hostName, $userName, $commands, null, $arguments);
    }

    private function prepareBackend(string $hostName, string $userName, array $arguments)
    {
        $commands = [
            'cd ~/branches/{{hostName}}/www',
            'composer install',
            'php artisan migrate',
        ];
        $this->executeRemoteCommands($hostName, $userName, $commands, null, $arguments);
    }

    private function prepareFrontend(string $hostName, string $userName, array $arguments)
    {
        $commands = [
            'cd ~/branches/{{hostName}}/www',
            'npm install',
            'npm run dev',
        ];
        $this->executeRemoteCommands($hostName, $userName, $commands, null, $arguments);
    }

    private function pullBranchOnStagingServer(string $hostName, string $userName, array $arguments)
    {
        $commands = [
            'cd ~/branches/{{hostName}}/www',
            'git pull',
        ];
        $this->executeRemoteCommands($hostName, $userName, $commands, null, $arguments);
    }

    private function restoreDatabase(string $hostName, string $userName, array $arguments)
    {
        $commands = [
            'cd ~/branches/{{hostName}}/www',
            'mysql -u{{mysqlRootUser}} -p{{mysqlRootPassword}} -e \"DROP DATABASE IF EXISTS \\\\\`{{branchName}}\\\\\`;\"',
            'mysql -u{{mysqlRootUser}} -p{{mysqlRootPassword}} -e \"CREATE USER IF NOT EXISTS \'{{branchName}}\'@\'localhost\' IDENTIFIED BY \'stage_password\';\"',
            'mysql -u{{mysqlRootUser}} -p{{mysqlRootPassword}} -e \"CREATE DATABASE IF NOT EXISTS \\\\\`{{branchName}}\\\\\`;\"',
            'mysql -u{{mysqlRootUser}} -p{{mysqlRootPassword}} -e \"GRANT ALL ON \\\\\`{{branchName}}\\\\\`.* TO \'{{branchName}}\'@\'localhost\';\"',
            'aws s3 cp s3://delo-backup/db-backup/mysql-full/latest.sql ./{{branchName}}.sql',
            'mysql -u{{branchName}} -pstage_password --database={{branchName}} < ./{{branchName}}.sql',
            'rm ./{{branchName}}.sql',
        ];
        $this->executeRemoteCommands($hostName, $userName, $commands, null, $arguments);
    }

    public function handle()
    {
        $full = $this->option('full');

        $deployDomain = env('DEPLOY_DOMAIN');
        $deployUser = env('DEPLOY_USER');
        $deployRepository = env('DEPLOY_REPOSITORY');
        $mysqlRootUser = env('MYSQL_ROOT_USER');
        $mysqlRootPassword = env('MYSQL_ROOT_PASSWORD');

        $branchName = $this->getCurrentBranchName();
        $branchHostName = $deployDomain . '.' . $branchName;


        $this->line('Начинаю деплой ветки «' . $branchName . '»');
        $workingDirectoryClean = $this->isWorkingDirectoryIsClean();
        if (!$workingDirectoryClean) {
            $message = '';
            while ($message == '') {
                $message = $this->ask('Перед деплоем нужно закоммитить изменения. Введите сообщение для коммита или нажмите Ctrl+C, чтобы прервать операцию. Сообщение для коммита');
            }
            $this->commit($message);
            $this->pushBranchToRemote($branchName);
        }
        if ($full) {
            $this->clearExistingBranch($branchHostName, $deployUser, []);
            $this->cloneBranchOnStagingServer($branchHostName, $deployUser, [
                'repositoryName' => $deployRepository,
                'branchName' => $branchName,
                'commentsPort' => $this->getRandomFreePortInRange($branchHostName, $deployUser, 3000, 3999),
                'collaborateEditorPort' => $this->getRandomFreePortInRange($branchHostName, $deployUser, 3000, 3999),

            ]);
            $this->line('Загружаю последнюю версию базы данных для «' . $branchName . '»');
            $this->restoreDatabase($branchHostName, $deployUser, [
                'mysqlRootUser' => $mysqlRootUser,
                'mysqlRootPassword' => $mysqlRootPassword,
                'branchName' => $branchName,
            ]);
        } else {
            $this->pullBranchOnStagingServer($deployRepository, $branchName, $deployUser, $deployDomain);
        }
        $this->prepareEnvFile($branchHostName, $deployUser, [
            'stubEnvFile' => '.env.deploy-per-branch.stub',
            'envFile' => '.env',
        ]);
        $this->prepareBackend($branchHostName, $deployUser, [

        ]);
        $this->line('Всё готово!');
        $this->line('Ветка доступна по адресу: https://' . $branchName . '.' . $deployDomain);
    }
}
