<?php

namespace Yoke\Console\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class DeleteCommand.
 *
 * Allow users to remove previously stored connection information.
 */
class DeleteCommand extends BaseCommand
{
    /**
     * @var string Command name.
     */
    protected $name = 'delete';

    /**
     * @var string Command description.
     */
    protected $description = 'Remove a connection configuration.';

    /**
     * @var array Command arguments.
     */
    protected $arguments = [
        // Connection alias.
        ['alias', InputArgument::REQUIRED, 'The connection to be removed.'],
    ];

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     */
    protected function fire(InputInterface $input)
    {
        // Find the server.
        $alias = $this->argument('alias');
        // Ensure server exists.
        $this->manager->getServer($alias);

        // Greetings.
        $this->info('Server connection removal.');

        // Ask for confirmation.
        $confirmed = $this->askConfirmation("Are you sure about deleting the connection {$alias}:");

        // If confirmed.
        if ($confirmed) {
            // Delete the connection.
            $this->manager->deleteServer($alias);
            // And congratulate.
            $this->info('Server connection deleted successfully!');
        }
    }
}
