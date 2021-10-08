<?php

namespace Yoke\Console\Commands;

use Symfony\Component\Console\Input\{InputArgument, InputInterface};

/**
 * Class ConnectCommand
 *
 * @package Yoke\Console\Commands
 */
class ConnectCommand extends BaseCommand
{
    protected string $name = 'connect';
    protected string $description = 'Connect into a saved configuration';

    /** @var array Command arguments. */
    protected array $arguments = [
        // Connection alias.
        ['alias', InputArgument::REQUIRED, 'Connection Alias'],
    ];

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     */
    protected function fire(InputInterface $input): void
    {
        // Gets the desired connection alias.
        $alias = $this->argument('alias');
        // Finds the store server connection using the provided alias.
        $server = $this->manager->getServer($alias);

        if (null === $server) {
            $this->writeln('Nah!');

            return;
        }

        // Write the console line to be executed on the bash side of
        // the string. sometimes it will contain a password
        // for usage while authenticating
        $this->writelnPlain($server->connectionString());
    }
}
