<?php

namespace yanivgal;

use SuperClosure\Serializer;

class CronomSerializer
{
    /**
     * @var string
     */
    private static $encryptMethod = 'aes128';

    /**
     * @var string
     */
    private static $encryptIV = 'bnpfuq8eryvpq34f';

    /**
     * @param callable $job
     * @return string
     */
    public static function serialize($job)
    {
        $serializer = new Serializer();
        $serializedJob = $serializer->serialize($job);
        return openssl_encrypt(
            $serializedJob,
            self::$encryptMethod,
            self::$encryptMethod,
            false,
            self::$encryptIV
        );
    }

    /**
     * @param string $serializedJob
     * @return callable
     */
    public static function deserialize($serializedJob)
    {
        $serializedJob = openssl_decrypt(
            $serializedJob,
            self::$encryptMethod,
            self::$encryptMethod,
            false,
            self::$encryptIV
        );
        $serializer = new Serializer();
        return $serializer->unserialize($serializedJob);
    }
}