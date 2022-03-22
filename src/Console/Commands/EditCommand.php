<?php

namespace Yoke\Console\Commands;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class EditCommand.
 *
 * Displays a list of server connections to the user.
 */
class EditCommand extends BaseCommand
{
    protected string $name = 'edit';
    protected string $description = 'Edit server details';

    public function __construct()
    {
        $this->arguments = [
            new InputArgument('alias', InputArgument::REQUIRED, 'Connection alias'),
        ];

        parent::__construct();
    }

    /**
     * Execute the command.
     *
     * @param InputInterface $input
     *
     * @return int
     *
     * @throws \Exception
     */
    protected function fire(InputInterface $input): int
    {
        // Gets the desired connection alias.
        $alias = $this->argument('alias');
        // Finds the store server connection using the provided alias.
        $server = $this->manager->getServer($alias);

        if (null === $server) {
            $this->writeln('Nah!');

            return self::INVALID;
        }

        $oldData = $server->toArray();

        $server->user = $this->ask(sprintf('Server username (%s):', $server->user), $server->user);
        $server->host = $this->ask(sprintf('Server hostname or IP Address (%s):', $server->host), $server->host);
        $server->port = $this->ask(sprintf('Server Port (%s):', $server->port), $server->port);
        $server->authenticationMethod = $this->ask(
            sprintf('Authentication Method [system|key|password]: (%s):', $server->authenticationMethod),
            $server->authenticationMethod
        );

        $newData = $server->toArray();

        $table = new Table($this->output);
        $table->setHeaders(['Field', 'Old', 'New']);

        $rows = [];

        foreach (array_keys($newData) as $field) {
            $rows[] = [$field, $oldData[$field], $newData[$field]];
        }

        $table->setRows($rows);
        $table->render();

        if ($this->askConfirmation('Confirm the changes?')) {
            $this->manager->createServer($server->toArray());

            $this->info('Done! :D');

            return self::SUCCESS;
        }

        $this->writeln('');

        return self::SUCCESS;
    }
}
