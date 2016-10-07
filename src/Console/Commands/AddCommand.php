<?php

namespace Yoke\Console\Commands;

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
    protected $name = 'add';

    /**
     * @var string Command description.
     */
    protected $description = 'Store a new connection configuration.';

    /**
     * Execute the command.
     */
    protected function fire()
    {
        // Greetings.
        $this->info('Registering a new Server Configuration!');

        // Read initial connection data.
        $serverData['alias'] = $this->ask('Server connection alias (server1):', 'server1');
        $serverData['user'] = $this->ask('Server username (none):');
        $serverData['host'] = $this->ask('Server hostname or IP Address (192.168.0.1):', '192.168.0.1');
        $serverData['port'] = $this->ask('Server Port (22):', 22);
        $serverData['authenticationMethod'] = $this->ask(
            'Authentication Method:[system|key|password] (system):',
            'system'
        );

        if ($serverData['authenticationMethod'] == 'key') {
            // Ask for private key if key was selected as authentication method.
            $serverData['privateKey'] = $this->ask('Private Key (~/.ssh/id_rsa):', $_SERVER['HOME'].'/.ssh/id_rsa');
        } elseif ($serverData['authenticationMethod'] == 'password') {
            // Ask for password if password as selected as authentication method.
            $serverData['password'] = $this->ask('Password:');
        } else {
            // don't store anything when system is selected.
        }

        // Register the server connection data into servers manager.
        $this->manager->createServer($serverData);

        // If the server was indeed created, congratulate the user.
        if ($this->manager->serverExists($serverData['alias'])) {
            $this->comment('Server registered successfully!');
        }
    }
}
