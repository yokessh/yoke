<?php

namespace Yoke\Console\Commands;

use Exception;
use Symfony\Component\Console\Input\{InputArgument, InputInterface};

/**
 * Class AddCommand.
 *
 * Registers new server connections into Yoke.
 */
class AddCommand extends BaseCommand
{
    /**
     * @var string Command name.
     */
    protected string $name = 'add';

    /**
     * @var string Command description.
     */
    protected string $description = 'Store a new connection configuration.';

    protected array $arguments = [
        ['alias', InputArgument::OPTIONAL, 'Connection Alias'],
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
        // Greetings.
        $this->info('Registering a new Server Configuration!');

        // Read initial connection data.
        if (!$serverData['alias'] = $input->getArgument('alias')) {
            $serverData['alias'] = $this->ask('Server connection alias (server1):', 'server1');
        }

        $serverData['user'] = $this->ask('Server username (none):');
        $serverData['host'] = $this->ask('Server hostname or IP Address (192.168.0.1):', '192.168.0.1');
        $serverData['port'] = $this->ask('Server Port (22):', 22);
        $serverData['authenticationMethod'] = $this->ask(
            'Authentication Method:[system|key|password] (system):',
            'system'
        );

        if ('key' === $serverData['authenticationMethod']) {
            // Ask for private key if key was selected as authentication method.
            $serverData['privateKey'] = $this->ask('Private Key (~/.ssh/id_rsa):', $_SERVER['HOME'] . '/.ssh/id_rsa');
        }

        if ('password' === $serverData['authenticationMethod']) {
            // Ask for password if password as selected as authentication method.
            $serverData['password'] = $this->ask('Password:');
        }

        // Register the server connection data into servers manager.
        $this->manager->createServer($serverData);

        // If the server was indeed created, congratulate the user.
        if ($this->manager->serverExists($serverData['alias'])) {
            $this->comment('Server registered successfully!');
        }
    }
}
