<?php

namespace yanivgal;

use Closure;
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
     * @var string
     */
    private static $serializedTag = '[[serialized]]';

    /**
     * @param callable $job
     * @return string
     */
    public static function serialize($job)
    {
        $serializedJob = $job;
        if (self::isClosure($serializedJob)) {
            $serializer = new Serializer();
            $serializedJob = $serializer->serialize($job);
            $serializedJob .= self::$serializedTag;
        }
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

        if (self::endswith($serializedJob, self::$serializedTag)) {
            $serializedJob = rtrim($serializedJob, self::$serializedTag);
            $serializer = new Serializer();
            $serializedJob = $serializer->unserialize($serializedJob);
        }
        
        return $serializedJob;
    }

    /**
     * @param mixed $c
     * @return bool
     */
    private static function isClosure($c)
    {
        return is_object($c) && ($c instanceof Closure);
    }

    /**
     * @param string $string
     * @param string $test
     * @return bool
     */
    private static function endswith($string, $test)
    {
        $strlen = strlen($string);
        $testlen = strlen($test);
        if ($testlen > $strlen) {
            return false;
        }
        return substr_compare($string, $test, $strlen - $testlen, $testlen) === 0;
    }
}