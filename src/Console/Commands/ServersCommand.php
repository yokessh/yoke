<?php

namespace Yoke\Console\Commands;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Yoke\Servers\Exceptions\NotFoundException;
use Yoke\Servers\Server;

/**
 * Class ServersCommand.
 *
 * Displays a list of server connections to the user.
 */
class ServersCommand extends BaseCommand
{
    protected string $name = 'servers';
    protected string $description = 'List available servers';

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     *
     * @return int
     */
    protected function fire(InputInterface $input): int
    {
        // Get the available servers.
        $servers = $this->manager->getServers();

        // If there is no servers registered.
        // @todo: this shouldn't be an error, just a warning maybe
        if (!count($servers)) {
            throw new NotFoundException('No servers available.');
        }

        // Render the servers table.
        $this->serversTable($servers);

        return self::SUCCESS;
    }

    /**
     * Renders the servers' table into console.
     *
     * @param array $servers
     */
    protected function serversTable(array $servers): void
    {
        // New table instance.
        $table = new Table($this->output);
        // Set table headers.
        $table->setHeaders(['Name', 'Host', 'Username', 'Port', 'Auth. Method', 'SSH Option']);

        // Loop on available connections to build the rows.
        $rows = [];

        /** @var Server $server */
        foreach ($servers as $server) {
            $rows[] = [
                $server->alias,
                $this->isIP($server->host) ? "<fg=yellow>{$server->host}</>" : $server->host,
                $server->user,
                "<fg=yellow>{$server->port}</>",
                $server->authenticationMethod,
                $server->sshOption,
            ];
        }

        // Set the table rows
        $table->setRows($rows);
        // Render the table.
        $table->render();
    }

    private function isIP(string $host): bool
    {
        return (bool)filter_var($host, FILTER_VALIDATE_IP);
    }
}
