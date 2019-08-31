<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 26.07.19 22:57
 *
 */

namespace WHMCS\Module\Addon\TeamSpeakBackaup\Abstracts;

use WHMCS\Module\Addon\TeamSpeakBackaup\Configs\ModuleConfig;
use WHMCS\Module\Addon\TeamSpeakBackaup\Controllers\CompressBzip2Controller;
use WHMCS\Module\Addon\TeamSpeakBackaup\Controllers\CompressGzipController;
use WHMCS\Module\Addon\TeamSpeakBackaup\Controllers\CompressNoneController;

abstract class CompressManagerFactoryAbstract
{
    /**
     * @param string $c
     * @return CompressBzip2Controller|CompressGzipController|CompressNoneController
     * @throws  \Exception
     */
    public static function create($c)
    {
        $c = ucfirst(strtolower($c));
        if (!CompressMethodAbstract::isValid($c)) {
            throw new \Exception("Compression method ($c) is not defined yet");
        }

        $method = "WHMCS\\Module\\Addon\\" . ModuleConfig::getModuleName() . "\\Controllers\\Compress" . $c . 'Controller';

        return new $method;
    }
}