<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 26.07.19 22:59
 *
 */

namespace WHMCS\Module\Addon\TeamSpeakBackaup\Controllers;

use WHMCS\Module\Addon\TeamSpeakBackaup\Abstracts\CompressManagerFactoryAbstract;
use WHMCS\Module\Addon\TeamSpeakBackaup\Interfaces\CompressInterface;

class CompressNoneController extends CompressManagerFactoryAbstract implements CompressInterface
{
    private $fileHandler = null;

    /**
     * @param string $filename
     * @param string $mode
     * @return boolean
     * @throws \Exception
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
     * @param string $data
     * @param int $level
     * @return string
     */
    public function compressString(string $data, int $level = 9): string
    {
        return $data;
    }

    /**
     * @param string $data
     * @return string
     */
    public function decompressString(string $data): string
    {
        return $data;
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

    /**
     * @return string
     */
    function getFileExtension(): string
    {
        return '';
    }

}