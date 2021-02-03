<?php

namespace Yoke\Console\Commands;

use Exception;
use Symfony\Component\Console\Input\{InputArgument, InputInterface};

/**
 * Class DeleteCommand.
 *
 * Allow users to remove previously stored connection information.
 */
class DeleteCommand extends BaseCommand
{
    /** @var string Command name. */
    protected string $name = 'delete';
    /** @var string Command description. */
    protected string $description = 'Remove a connection configuration.';

    /** @var array|array[] Command arguments. */
    protected array $arguments = [
        ['alias', InputArgument::REQUIRED, 'The connection to be removed.'],
    ];

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     *
     * @throws Exception
     */
    protected function fire(InputInterface $input): void
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
