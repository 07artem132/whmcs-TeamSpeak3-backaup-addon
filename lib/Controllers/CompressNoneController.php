<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 26.07.19 22:59
 *
 */

namespace WHMCS\Module\Addon\TeamSpeakBackaup\Controllers;

use WHMCS\Module\Addon\TeamSpeakBackaup\Abstracts\CompressManagerFactoryAbstract;

class CompressNoneController extends CompressManagerFactoryAbstract
{
    private $fileHandler = null;

    /**
     * @param string $filename
     * @param string $mode
     * @throws \Exception
     * @return boolean
     */
    public function open($filename, $mode = 'wb')
    {
        $this->fileHandler = fopen($filename, $mode);
        if (false === $this->fileHandler) {
            throw new \Exception("Output file is not writable");
        }

        return true;
    }

    /**
     * @param $str
     * @return bool|int
     * @throws \Exception
     */
    public function write($str)
    {
        if (false === ($bytesWritten = fwrite($this->fileHandler, $str))) {
            throw new \Exception("Writting to file failed! Probably, there is no more free space left?");
        }
        return $bytesWritten;
    }

    /**
     * @return bool
     */
    public function close()
    {
        return fclose($this->fileHandler);
    }
}