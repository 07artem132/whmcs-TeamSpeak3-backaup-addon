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
    public static $enums = array(
        'None',
        'Gzip',
        'Bzip2',
    );

    /**
     * @param string $c
     * @return boolean
     */
    public static function isValid($c)
    {
        return in_array($c, self::$enums);
    }
}