<?php

namespace Qortex\Bootstrap\Commands\Traits;

trait ManagesRemotePorts
{
    private function extractOpenPortFromHostNameAndPort(string $hostNameAndPort)
    {
        list($host, $port) = explode(':', $hostNameAndPort);
        return $port;
    }

    private function extractOpenPortFromLine(string $line)
    {
        $values = preg_split('~[\t\s]+~', $line, -1, PREG_SPLIT_NO_EMPTY);
        if (count($values) > 4) {
            return $this->extractOpenPortFromHostNameAndPort($values[4]);
        }
        return 0;
    }

    private function extractOpenPortsFromLines(array $lines)
    {
        $openPorts = [];
        foreach ($lines as $line) {
            $openPorts[] = $this->extractOpenPortFromLine($line);
        }
        return $openPorts;
    }

    private function getOpenPorts(string $hostName, string $userName)
    {
        $commands = [
            'ss -tulwn | grep LISTEN',
        ];
        $lines = $this->executeRemoteCommands($hostName, $userName, $commands);
        return $this->extractOpenPortsFromLines($lines);
    }

    private function getRandomFreePortInRange(string $hostName, string $userName, int $min, int $max)
    {
        $openPorts = $this->getOpenPorts($hostName, $userName);
        $possiblePorts = range($min, $max);
        $freePorts = array_diff($possiblePorts, $openPorts);
        return min($freePorts);
    }

    private function stopServerByPort(string $hostName, string $userName, int $port, $force = false)
    {
        $killSwitch = $force ? '-9' : '';
        $commands = [
            'kill {{killSwitch}} \$(lsof -t -i:{{port}})',
        ];
        $this->executeRemoteCommands($hostName, $userName, $commands, '', [
            'port' => $port,
            'killSwitch' => $killSwitch,
        ]);
    }
}
