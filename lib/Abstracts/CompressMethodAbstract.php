<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 26.07.19 22:59
 *
 */

namespace WHMCS\Module\Addon\TeamSpeakBackaup\Abstracts;

/**
 * Enum with all available compression methods
 *
 */
abstract class CompressMethodAbstract
{
    public static $enums = [
        'None',
        'Gzip',
        'Bzip2',
    ];
    public static $extensionToMethod = [
        'gz' => 'Gzip',
        'bz2' => 'Bzip2',
    ];

    /**
     * @param string $c
     * @return boolean
     */
    public static function isValid(string $c): bool
    {
        return in_array($c, self::$enums);
    }

    public static function isValidExtension(string $extension): bool
    {
        return array_key_exists($extension, self::$extensionToMethod);
    }

    public static function getMethodForExtension(string $extension): string
    {
        return self::$extensionToMethod[$extension];
    }
}