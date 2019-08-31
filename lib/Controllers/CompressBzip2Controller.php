<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 26.07.19 22:59
 *
 */

namespace WHMCS\Module\Addon\TeamSpeakBackaup\Controllers;

use WHMCS\Module\Addon\TeamSpeakBackaup\Abstracts\CompressManagerFactoryAbstract;

class CompressBzip2Controller extends CompressManagerFactoryAbstract
{
    private $fileHandler = null;

    /**
     * CompressBzip2 constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        if (!function_exists("bzopen")) {
            throw new \Exception("Compression is enabled, but bzip2 lib is not installed or configured properly");
        }
    }

    /**
     * @param string $filename
     * @param string $mode
     * @throws \Exception
     * @return boolean
     */
    public function open($filename, $mode = 'w')
    {
        $this->fileHandler = bzopen($filename, $mode);
        if (false === $this->fileHandler) {
            throw new \Exception("Output file is not writable");
        }

        return true;
    }

    /**
     * @param $str
     * @return int
     * @throws \Exception
     */
    public function write($str)
    {
        if (false === ($bytesWritten = bzwrite($this->fileHandler, $str))) {
            throw new \Exception("Writting to file failed! Probably, there is no more free space left?");
        }
        return $bytesWritten;
    }

    /**
     * @return int
     */
    public function close()
    {
        return bzclose($this->fileHandler);
    }
}
