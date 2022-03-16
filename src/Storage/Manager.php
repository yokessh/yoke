<?php

namespace Yoke\Storage;

use Symfony\Component\Yaml\{Dumper, Parser};
use Exception;
use JsonException;

/**
 * Class Manager.
 *
 * Storage Manager provides a encrypted data storage using YAML files.
 */
class Manager
{
    protected Encrypter $encrypter;

    /**
     * Manager constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        // New encrypter instance.
        $this->encrypter = new Encrypter($this->getOrGenerateKey());
    }

    /**
     * Get a existing or create a new encryption key.
     *
     * This method reads the encryption key of the encryption.key file, if this file
     * does not exists, it generates a new key using the encrypter static method
     * and then stores it.
     *
     * WARNING: If the encryption key is changed the stored values will not be able of
     * decryption.
     *
     * @return string The encryption key.
     *
     * @throws Exception
     */
    protected function getOrGenerateKey(): string
    {
        // Generates a new key is none exists.
        if (!$this->fileExists('encryption.key')) {
            $this->storeFile('encryption.key', Encrypter::generateKey());
        }

        // Return the encryption key.
        return trim($this->getContents('encryption.key'));
    }

    /**
     * Return a array of values for a given configuration file.
     * Defaults to servers.yml (the .yml extension should be omitted).
     *
     * @param string $type Configuration file name without .yml prefix.
     *
     * @return array Decrypted configuration array.
     *
     * @throws JsonException
     */
    public function getConfiguration(string $type = 'servers'): array
    {
        // If the requested configuration file exists.
        if ($this->fileExists("{$type}.yml")) {
            // Create a new YML parser instance.
            $parser = new Parser();
            // Gets the encrypted configuration array from YML.
            $encryptedConfiguration = $parser->parse($this->getContents("{$type}.yml"));

            return $this->encrypter->handleMultidimensionalArray($encryptedConfiguration, 'decrypt');
        }

        // Otherwise, just return an empty array.
        return [];
    }

    /**
     * Write a given array into a encrypted storage file.
     *
     * @param array $data The data to be stored.
     * @param string $type The actual filename without the .yml extension to store the information.
     *
     * @throws Exception
     */
    public function writeConfiguration(array $data, string $type = 'servers'): void
    {
        $encryptedArray = $this->encrypter->handleMultidimensionalArray($data, 'encrypt');
        // Transform the encrypted data into YAML.
        $dumper = new Dumper();
        $configuration = $dumper->dump($encryptedArray, 2);

        // Store the encrypted file.
        $this->storeFile("{$type}.yml", $configuration);
    }

    /**
     * @return string The storage base path.
     */
    public function basePath(): string
    {
        return "{$_SERVER['HOME']}/.yoke";
    }

    /**
     * Prepare the storage path in case the directory don't already exists.
     */
    protected function prepareBasePath(): void
    {
        if (!is_dir($this->basePath())) {
            mkdir($this->basePath());
        }
    }

    /**
     * Get the full path for a relative filename.
     *
     * @param null $file
     *
     * @return string
     */
    protected function path($file = null): string
    {
        // Check if the folder exists, create if don't.
        $this->prepareBasePath();

        // If a file name is provided, return it's full path.
        if ($file) {
            return "{$this->basePath()}/{$file}";
        }

        // Return the base path otherwise.
        return $this->basePath();
    }

    /**
     * Check for a file existence.
     *
     * @param string $file Relative file name.
     *
     * @return bool Exists or not.
     */
    protected function fileExists(string $file): bool
    {
        return file_exists($this->path($file));
    }

    /**
     * Create or replace a given file with the given contents.
     *
     * @param string $name File name.
     * @param string $contents File contents.
     */
    protected function storeFile(string $name, string $contents): void
    {
        file_put_contents($this->path($name), $contents);
    }

    /**
     * Reads a given file contents.
     *
     * @param string $name Desired file.
     *
     * @return string The file contents.
     */
    protected function getContents(string $name): string
    {
        return file_get_contents($this->path($name));
    }
}
