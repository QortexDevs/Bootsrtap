<?php

namespace Qortex\Bootstrap\Commands;

use Illuminate\Console\Command;

class DeployBranchCommand extends Command
{
	protected $signature = 'deploy {--renew}';
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
		$stubFile = app_path('/.env.deploy-per-branch.stub');
		$envFile = app_path('.env');
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
			'sed \"s/{{ applicationHost }}/{{branchName}}.{{domain}}/\" ' . $stubFile . ' > ' .$envFile,
			'php artisan migrate',
		];
		$arguments = array_map(function ($item) use ($repositoryName, $branchName, $userName, $domain) {
			return str_replace([
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
			$item);
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
			return str_replace([
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
			$item);
		}, $commands);

		$command = 'ssh -o StrictHostKeyChecking=no ' . $userName . '@' . $branchName . '.' . $domain . ' "' . implode(' && ', $arguments) . '"';
		exec($command, $output, $status);
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */

	public function handle()
	{
		$renew = $this->option('renew');

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
		if ($renew) {
			$this->pullBranchOnStagingServer($deployRepository, $currentBranchName, $deployUser, $deployDomain);
		} else {
			$this->cloneRepositoryOnStagingServer($deployRepository, $currentBranchName, $deployUser, $deployDomain);
		}
		$this->line('Всё готово!');
		$this->line('Ветка доступна по адресу: https://' . $currentBranchName . '.' . $deployDomain);
	}
}
