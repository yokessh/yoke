<?php

namespace Yoke\Console\Commands;

use Symfony\Component\Console\Input\{InputArgument, InputInterface, InputOption};

/**
 * Class ConnectCommand
 *
 * @package Yoke\Console\Commands
 */
class ConnectCommand extends BaseCommand
{
    protected string $name = 'connect';
    protected string $description = 'Connect to a saved connection';

    public function __construct()
    {
        $this->arguments = [
            new InputArgument('alias', InputArgument::REQUIRED, 'Connection alias'),
        ];
        $this->options = [
            new InputOption('password', null, InputOption::VALUE_OPTIONAL, 'Show password'),
            new InputOption('user', null, InputOption::VALUE_OPTIONAL, 'Alternative user'),
        ];

        parent::__construct();
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     *
     * @return int
     */
    protected function fire(InputInterface $input): int
    {
        // Gets the desired connection alias.
        $alias = $this->argument('alias');
        // Finds the store server connection using the provided alias.
        $server = $this->manager->getServer($alias);

        if (null === $server) {
            $this->writeln('Nah!');

            return self::FAILURE;
        }

        if ($user = $input->getOption('user')) {
            $server->user = $user;
        }

        // Write the console line to be executed on the bash side of
        // the string. sometimes it will contain a password
        // for usage while authenticating
        $this->writelnPlain($server->connectionString($input->getOption('password')));

        return self::SUCCESS;
    }
}
