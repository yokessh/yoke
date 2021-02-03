<?php

namespace Yoke\Servers;

use Exception;
use Yoke\Servers\Exceptions\NotFoundException;
use Yoke\Storage\Manager as StorageManager;

/**
 * Class Manager.
 *
 * Server connections manager.
 */
class Manager
{
    /** @var StorageManager Storage handling. */
    protected StorageManager $storageManager;
    /** @var array List of store server connections. */
    protected array $servers = [];

    /**
     * Manager constructor.
     */
    public function __construct()
    {
        // created a new Manager instance.
        $this->storageManager = new StorageManager();

        // Load servers from storage.
        $this->loadServers();
    }

    /**
     * Load the stored servers.
     */
    protected function loadServers(): void
    {
        // Get the servers configuration array from the servers.yml file.
        $servers = $this->storageManager->getConfiguration('servers');

        // Register each server configuration as a Server instance.
        foreach ($servers as $serverData) {
            $this->registerServer($serverData);
        }
    }

    /**
     * Register a configuration array as a server instance.
     *
     * @param array $data Server connection information.
     */
    public function registerServer(array $data): void
    {
        // Generate a new server connection instance.
        $server = new Server($data);

        // Register the server connection into the servers list.
        $this->servers[$server->alias] = $server;
    }

    /**
     * Create a server connection instance with the provided configuration
     * data and write the configuration server with the updated information.
     *
     * @param array $data Server connection information.
     *
     * @throws Exception
     */
    public function createServer(array $data): void
    {
        // Register a new server connection instance.
        $this->registerServer($data);
        // Write the configuration server with the updated information
        $this->writeServers();
    }

    /**
     * Deletes a server connection from instance and storage.
     *
     * @param string $alias Alias of the server connection to be deleted.
     *
     * @throws Exception
     */
    public function deleteServer(string $alias): void
    {
        // Find the server.
        $server = $this->getServer($alias);

        if ($server) {
            // Forget the server from the server connection instances list
            unset($this->servers[$server->alias]);

            // Write the updated configuration file
            $this->writeServers();
        }
    }

    /**
     * Write the current server instances into a servers.yml storage file.
     *
     * @throws Exception
     */
    protected function writeServers(): void
    {
        $servers = [];

        // Parse all current instances into the array representation.
        foreach ($this->servers as $server) {
            $servers[] = $server->toArray();
        }

        // Write the servers array into the servers.yml file.
        $this->storageManager->writeConfiguration($servers);
    }

    /**
     * Get a server instance.
     *
     * @param string $alias Server connection alias.
     *
     * @return Server The Server connection instance.
     *
     * @throws NotFoundException When the desired alias is not registered.
     */
    public function getServer(string $alias): ?Server
    {
        if ($this->serverExists($alias)) {
            return $this->servers[$alias];
        }

        throw new NotFoundException('Server not found.');
    }

    /**
     * Get all the server connection instances.
     *
     * @return array A array containing all the server connection instances.
     */
    public function getServers(): array
    {
        return $this->servers;
    }

    /**
     * Is a given connection alias registered?
     *
     * @param string $alias The given server connection instance alias.
     *
     * @return bool Registered or not.
     */
    public function serverExists(string $alias): bool
    {
        return array_key_exists($alias, $this->servers);
    }
}
