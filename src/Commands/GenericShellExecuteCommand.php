<?php

namespace Qortex\Bootstrap\Commands;

abstract class GenericShellExecuteCommand extends GenericCommand
{
    protected function getRemoveEnvValue($hostName, $userName, $envName)
    {
        $commands = [
            'cd ~/branches/{{hostName}}/www',
            'php artisan get:env-value {{envName}}',
        ];
        $output = $this->executeRemoteCommands($hostName, $userName, $commands, '', [
            'envName' => $envName
        ]);
        if (count($output) > 0) {
            return $output[0];
        }
        return null;
    }

    protected function executeRemoteCommands(string $hostName, string $userName, array $commands, string $password = null, array $arguments = [])
    {
        $arguments['hostName'] = $hostName;
        $arguments['userName'] = $userName;
        $arguments = array_map(function ($item) use ($arguments) {
            $variables = [];
            $replacements = [];
            foreach ($arguments as $argumentName => $argumentValue) {
                $variables[] = '{{' . $argumentName . '}}';
                $replacements[] = $argumentValue;
            }
            return str_replace($variables, $replacements, $item);
        }, $commands);
        $command = 'ssh -o StrictHostKeyChecking=no ' . $userName . '@' . $hostName . ' "' . implode(' && ', $arguments) . '"';
        exec($command, $output, $status);
        return $output;
    }
}
