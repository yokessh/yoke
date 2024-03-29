<?php

namespace Yoke\Servers;

/**
 * Class Server.
 *
 * Base class to represent a stored connection.
 *
 * @property string $alias
 * @property string $host
 * @property int $port
 * @property string $user
 * @property string $authenticationMethod
 * @property string $password
 * @property string $privateKey
 * @property string $sshOption
 */
class Server
{
    protected string $alias;
    protected string $host;
    protected int $port = 22;
    protected string $user;
    protected string $authenticationMethod = 'password';
    protected string $password = '';
    protected string $privateKey = '';
    protected string $sshOption = '';

    /**
     * Server constructor.
     *
     * @param array $config Stored values
     */
    public function __construct(array $config)
    {
        // For each config key
        foreach ($config as $key => $value) {
            // Set into its own attribute.
            $this->$key = $value;
        }
    }

    /**
     * Magic __set method.
     *
     * @param string $key Attribute name.
     * @param mixed $value Attribute value
     */
    public function __set(string $key, mixed $value)
    {
        // set the value into class attribute.
        $this->$key = $value;
    }

    /**
     * Magic __get method.
     *
     * @param string $key desired attribute.
     *
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->$key;
    }

    /**
     * @return string The port portion of the shh connection string.
     */
    protected function portParameter(): string
    {
        return $this->port ? "-p{$this->port}" : '';
    }

    /**
     * @return string The password helper line.
     */
    public function passwordHelper(): string
    {
        return "Password: {$this->password}";
    }

    /**
     * @return string User and hostname portion of the ssh connection string.
     */
    protected function userAndHostParameter(): string
    {
        if ($this->user) {
            return "{$this->user}@{$this->host}";
        }

        return $this->host;
    }

    /**
     * @return string Private key portion of the connection string.
     */
    protected function keyParameter(): string
    {
        if ('key' === $this->authenticationMethod) {
            return "-i{$this->privateKey}";
        }

        return '';
    }

    /**
     * @param bool|null $showPassword
     *
     * @return string The final ssh connection string.
     */
    public function connectionString(?bool $showPassword = false): string
    {
        $connectionString = sprintf(
            'ssh %s %s %s %s',
            $this->keyParameter(),
            $this->portParameter(),
            $this->userAndHostParameter(),
            $this->sshOption
        );

        if ('password' === $this->authenticationMethod && $showPassword) {
            $connectionString = "{$this->passwordHelper()}\n{$connectionString}";
        }

        return $connectionString;
    }

    /**
     * @return array Array encoded representation of the server connection.
     */
    public function toArray(): array
    {
        $configArray = [];

        if (isset($this->alias)) {
            $configArray['alias'] = $this->alias;
        }

        if (isset($this->host)) {
            $configArray['host'] = $this->host;
        }

        if (isset($this->port)) {
            $configArray['port'] = $this->port;
        }

        if (isset($this->user)) {
            $configArray['user'] = $this->user;
        }

        if (isset($this->authenticationMethod)) {
            $configArray['authenticationMethod'] = $this->authenticationMethod;
        }

        if (isset($this->password)) {
            $configArray['password'] = $this->password;
        }

        if (isset($this->privateKey)) {
            $configArray['privateKey'] = $this->privateKey;
        }

        if (isset($this->sshOption)) {
            $configArray['sshOption'] = $this->sshOption;
        }

        return $configArray;
    }
}
