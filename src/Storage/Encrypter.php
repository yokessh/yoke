<?php

namespace Yoke\Storage;

use Exception;
use JsonException;
use RuntimeException;

use function openssl_decrypt;
use function openssl_encrypt;

/**
 * Class Encrypter.
 *
 * Encryption class to make Yoke values safely stored.
 * Hardly based on Laravel's Crypt.
 */
class Encrypter
{
    /** @var string Encryption cipher */
    protected string $cipher = 'AES-256-CBC';
    /** @var int IV Size */
    protected int $ivSize = 16;
    /** @var string Encryption key */
    protected string $key;

    /**
     * Encrypter constructor.
     *
     * @param $key
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * Encrypt the given value.
     *
     * @param string $value
     *
     * @return string
     *
     * @throws Exception
     */
    public function encrypt(string $value): string
    {
        // Generate a IV.
        $iv = random_bytes($this->ivSize);
        // Encrypt the value.
        $value = openssl_encrypt(serialize($value), $this->cipher, $this->key, 0, $iv);

        // If the value was not encrypted successfully.
        if ($value === false) {
            // Throw a exception.
            throw new RuntimeException('Could not Encrypt the give value.');
        }

        // Calculate the HMAC.
        $mac = hash_hmac('sha256', $iv . $value, $this->key);
        // Encode IV into encodable format.
        $iv = base64_encode($iv);
        // Encode the IV, Value and HMAV into a JSON Payload that will be stored.
        $json = json_encode(compact('iv', 'value', 'mac'), JSON_THROW_ON_ERROR);

        // Check for the encoded json string
        if (!is_string($json)) {
            throw new RuntimeException('Could not Encrypt the give value.');
        }

        // return the encrypted and encoded payload
        return base64_encode($json);
    }

    /**
     * Decrypt a given value.
     *
     * @param string $payload The encrypted payload
     *
     * @return mixed The Decrypted value.
     *
     * @throws JsonException
     */
    public function decrypt(string $payload)
    {
        // Decode the json payload into a array.
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

    public function handleMultidimensionalArray(array $data, string $action): array
    {
        $return = [];

        $function = match ($action) {
            'encrypt' => 'encryptArray',
            'decrypt' => 'decryptArray',
            default => throw new RuntimeException('Not supported Encrypter action.'),
        };

        foreach ($data as $dataSet) {
            $return[] = $this->$function($dataSet);
        }

        return $return;
    }
}
