<?php

namespace Yoke\Storage;

use Exception;
use JsonException;
use RuntimeException;

use function openssl_decrypt;
use function openssl_encrypt;

/**
 * Class Encryptor.
 *
 * Encryption class to make Yoke values safely stored.
 * Hardly based on Laravel's Crypt.
 */
class Encryptor
{
    /** @var string Encryption cipher */
    protected string $cipher = 'AES-256-CBC';
    /** @var int IV Size */
    protected int $ivSize = 16;

    public function __construct(protected string $key)
    {
    }

    /**
     * Encrypt the given value.
     *
     * @param mixed $payload
     *
     * @return string
     *
     * @throws Exception
     */
    public function encrypt(mixed $payload): string
    {
        // Generate a IV.
        $originalIv = openssl_random_pseudo_bytes($this->ivSize);
        // Encrypt the value.
        $value = openssl_encrypt(serialize($payload), $this->cipher, $this->key, 0, $originalIv);

        // If the value was not encrypted successfully.
        if ($value === false) {
            // Throw an exception.
            throw new RuntimeException('Could not Encrypt the give value.');
        }

        $data = $originalIv . $value;
        // Calculate the HMAC.
        $mac = hash_hmac('sha256', $data, $this->key);
        // Encode IV into encodable format.
        $iv = base64_encode($originalIv);
        // Encode the IV, Value and HMAC into a JSON Payload that will be stored.
        $json = json_encode(compact('iv', 'value', 'mac'), JSON_THROW_ON_ERROR);

        // Check for the encoded json string
        if (!is_string($json)) {
            throw new RuntimeException('Could not encrypt the given data.');
        }

        // return the encrypted and encoded payload
        return base64_encode($json);
    }

    /**
     * Decrypt a given value.
     *
     * @param mixed $payload The encrypted payload
     *
     * @return mixed The Decrypted value.
     *
     * @throws JsonException
     */
    public function decrypt(mixed $payload): mixed
    {
        // Decode the json payload into an array.
        $payload = json_decode(base64_decode($payload), true, 512, JSON_THROW_ON_ERROR);
        // Get the IV from the payload.
        $iv = base64_decode($payload['iv']);
        // Decrypt the value using the key and IV
        $decrypted = openssl_decrypt($payload['value'], $this->cipher, $this->key, 0, $iv);

        // If the value was not correctly encrypted
        if ($decrypted === false) {
            // Throw an exception
            throw new RuntimeException('Could not decrypt the given payload.');
        }

        // return the decrypted value.
        return unserialize($decrypted);
    }

    /**
     * Static Key generation method.
     *
     * @return string A random encryption key.
     *
     * @throws Exception
     */
    public static function generateKey(): string
    {
        return base64_encode(random_bytes(32));
    }

    /**
     * Decrypt a array of values.
     *
     * @param array $data Encrypted array payload.
     *
     * @return array Decrypted array.
     *
     * @throws JsonException
     */
    public function decryptArray(array $data): array
    {
        $decryptedArray = [];

        foreach ($data as $key => $payload) {
            $decryptedArray[$key] = $this->decrypt($payload);
        }

        return $decryptedArray;
    }

    /**
     * Encrypt a given array.
     *
     * @param array $data Array to be encrypted.
     *
     * @return array Encrypted array.
     *
     * @throws Exception
     */
    public function encryptArray(array $data): array
    {
        $encryptedArray = [];

        foreach ($data as $key => $value) {
            $encryptedArray[$key] = $this->encrypt($value);
        }

        return $encryptedArray;
    }
}
