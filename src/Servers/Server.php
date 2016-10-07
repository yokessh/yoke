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
 */
class Server
{
    /**
     * @var string Connection alias.
     */
    protected $alias;

    /**
     * @var string Server hostname or IP Address.
     */
    protected $host;

    /**
     * @var int TCP Connection port.
     */
    protected $port = 22;

    /**
     * @var string Server username.
     */
    protected $user;

    /**
     * @var string Authentication method (key|password|system).
     */
    protected $authenticationMethod = 'password';

    /**
     * @var string Connection password.
     */
    protected $password;

    /**
     * @var string Connection private key.
     */
    protected $privateKey;

    /**
     * Server constructor.
     *
     * @param array $config Stored values
     */
    public function __construct(array $config)
    {
        // For each config key
        foreach ($config as $key => $value) {
            // Set into it's own attribute.
            $this->$key = $value;
        }
    }

    /**
     * Magic __set method.
     *
     * @param string $key Attribute name.
     * @param mixed $value Attribute value
     */
    public function __set($key, $value)
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
    public function __get($key)
    {
        return $this->$key;
    }

    /**
     * @return string The port portion of the shh connection string.
     */
    protected function portParameter()
    {
        return $this->port ? "-p{$this->port}" : '';
    }

    /**
     * @return string The password helper line.
     */
    protected function passwordHelper()
    {
        return "Password: {$this->password}";
    }

    /**
     * @return string User and hostname portion of the ssh connection string.
     */
    protected function userAndHostParameter()
    {
        if ($this->user) {
            return "{$this->user}@{$this->host}";
        }

        return $this->host;
    }

    /**
     * @return string Private key portion of the connection string.
     */
    protected function keyParameter()
    {
        if ($this->authenticationMethod == 'key') {
            return "-i{$this->privateKey}";
        }

        return '';
    }

    /**
     * @return string The final ssh connection string.
     */
    public function connectionString()
    {
        $connectionString = "ssh {$this->keyParameter()} {$this->portParameter()} {$this->userAndHostParameter()}";

        if ($this->authenticationMethod == 'password') {
            $connectionString = $this->passwordHelper()."\n".$connectionString;
        }

        return $connectionString;
    }

    /**
     * @return array Array encoded representation of the server connection.
     */
    public function toArray()
    {
        $configArray = [];

        if ($this->alias) {
            $configArray['alias'] = $this->alias;
        }

        if ($this->host) {
            $configArray['host'] = $this->host;
        }

        if ($this->port) {
            $configArray['port'] = $this->port;
        }

        if ($this->user) {
            $configArray['user'] = $this->user;
        }

        if ($this->authenticationMethod) {
            $configArray['authenticationMethod'] = $this->authenticationMethod;
        }

        if ($this->password) {
            $configArray['password'] = $this->password;
        }

        if ($this->privateKey) {
            $configArray['privateKey'] = $this->privateKey;
        }

        return $configArray;
    }
}
