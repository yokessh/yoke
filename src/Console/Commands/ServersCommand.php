<?php

namespace Yoke\Console\Commands;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Yoke\Servers\Exceptions\NotFoundException;

/**
 * Class ServersCommand.
 *
 * Displays a list of server connections to the user.
 */
class ServersCommand extends BaseCommand
{
    /**
     * @var string Command name.
     */
    protected $name = 'servers';

    /**
     * @var string Command description.
     */
    protected $description = 'List the available servers.';

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     */
    protected function fire(InputInterface $input)
    {
        // Get the available servers.
        $servers = $this->manager->getServers();

        // If there is no servers registered.
        if (!count($servers)) {
            throw new NotFoundException('No servers available.');
            // Otherwise.
        } else {
            // Render the servers table.
            $this->serversTable($servers);
        }
    }

    /**
     * Renders the servers table into console.
     *
     * @param array $servers
     */
    protected function serversTable(array $servers)
    {
        // New table instance.
        $table = new Table($this->output);

        // Set table headers.
        $table->setHeaders(['Name', 'Host', 'Username', 'Port', 'Auth. Method']);

        // Loop on available connections to build the rows.
        $rows = [];

        foreach ($servers as $server) {
            $rows[] = [$server->alias, $server->host, $server->user, $server->port, $server->authenticationMethod];
        }

        // Set the table rows
        $table->setRows($rows);

        // Render the table.
        $table->render();
    }
}
