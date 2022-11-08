## Yoke: SSH Connection Manager

Yoke is a PHP based **SSH connection manager**. Sometimes storing servers hosts, usernames, ports and passwords can be tricky, SSH Key authentication makes it easier for us, but it doesn't solve the problem of remembering all the other information.
Also, sometimes we face ourselves with more than one private key to authenticate with (like multiple accounts on AWS).

Yoke aims to be a single repository for server managements to allow you to fastly connect to your servers just by remembering it's alias, like.

```shell
yoke connect myserver
```

With security in mind, all information about your servers is encrypted using **AES 256**.

**NOTICE** The encryption key is also stored into your computer, Yoke encryption only makes it harder for users to identify and decrypt the information. But just like SSH private keys, it does not protect against people getting access to your filesystem.

### Installation

In order to use Yoke, you need PHP 8+ installed, with openssl extension enables (default on most installs)

The installation process is based on the global composer packages, so you need to have a working composer install with the correct binary path settings. [Read this tutorial ](https://akrabat.com/global-installation-of-php-tools-with-composer/)

If you have the requirements, install Yoke by running:

```shell
composer global require yokessh/yoke
```

This is all you need to do! Time for usage instructions.

### Usage

Using Yoke is really simple and straightforward.

#### Adding a Server Connection

In order to store a new connection, just run the command

```shell
yoke add [alias]
```

You will then be presented with a few questions:

```
Registering a new Server Configuration!

Server connection alias (server1): sample-server

👤 Server username (none): sample-user

🖥️ Server hostname or IP Address (192.168.0.1): server.sampleapp.com

🚪 Server Port (22): 6262

🔐 Authentication Method:[system|key|password] (system): key

🔑 Private Key (~/.ssh/id_rsa):

Server registered successfully! 🥳
```

#### Connecting

As we have this connection in place, we can establish a connection, anytime we want just by running a simple command:

```shell
yoke connect sample-server
```

In case it's a server with a password, you can optionally ask to show the password when connecting:

```shell
yoke connect sample-server --password
```

Easy right?

#### Listing connections

Forgot a server alias? Don't worry, you can just run:

```shell
yoke servers
```

To see a list of stored connections, like this one

```markdown
+---------------+----------------------+-------------+------+--------------+
| Name          | Host                 | Username    | Port | Auth. Method |
+---------------+----------------------+-------------+------+--------------+
| server-a      | a.sampleapp.comm     | admin       | 22   | key          |
| server-b      | b.sampleapp.com      | root        | 2222 | system       |
| server-c      | c.sampleapp.com      | root        | 22   | password     |
+---------------+----------------------+-------------+------+--------------+
```

#### Removing a connection
Don't need a stored connection anymore?

Just run

```shell
yoke delete alias
```

Confirm the deletion and it's done!.


#### Final Notes:
There are 3 different allows authentication types:

- `key` - uses a specified private key to establish the connection
- `system` - Do not specify a private key to connect, it lets ssh try to connection with current user's key
- `password` - SSH does not allow passing plain password as a parameter, Yoke will just show the password on screen, so you can copy and paste it. **Password authentication is highly unrecommended**.
