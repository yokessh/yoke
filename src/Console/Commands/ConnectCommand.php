<?php

namespace Yoke\Console\Commands;

use Symfony\Component\Console\Input\{InputArgument, InputInterface};
use Symfony\Component\Process\Process;
use Yoke\Servers\Exceptions\NotFoundException;

class ConnectCommand extends BaseCommand
{
    /**
     * @var string Command name.
     */
    protected string $name = 'connect';

    /**
     * @var string Command description.
     */
    protected string $description = 'Connect into a saved configuration';

    /**
     * @var array Command arguments.
     */
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

        // Write the console line to be executed on the bash side of
        // the string. sometimes it will contain a password
        // for usage while authenticating
        $this->writelnPlain($server->connectionString());
    }
}
