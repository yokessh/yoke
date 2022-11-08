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

        $oldData = $newData = $server->toArray();

        $newData['user'] = $this->ask(sprintf('👤 Server username (current: %s):', $server->user), $server->user);
        $newData['host'] = $this->ask(
            sprintf('🖥️ Server hostname or IP Address (current: %s):', $server->host),
            $server->host
        );
        $newData['port'] = $this->ask(sprintf('🚪 Server Port (current: %s):', $server->port), $server->port);
        $newData['authenticationMethod'] = $this->ask(
            sprintf('🔐 Authentication Method [system|key|password]: (current: %s):', $server->authenticationMethod),
            $server->authenticationMethod
        );

        if ('key' === $server->authenticationMethod) {
            // Ask for private key if key was selected as authentication method.
            $newData['privateKey'] = $this->ask("🔑 Private Key (current: {$server->privateKey}):", $server->privateKey);
        }

        if ($this->askConfirmation(
            "🗄️ Is there any SSH option you would like to add? (eg: -o 'PubkeyAcceptedKeyTypes +ssh-rsa')"
        )) {
            $newData['sshOption'] = $this->ask("Option (current: {$server->sshOption}):", $server->sshOption);
        }

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

            $this->info('Done! 🥳');

            return self::SUCCESS;
        }

        $this->writeln('Nothing has been changed. 🙃');

        return self::SUCCESS;
    }
}
