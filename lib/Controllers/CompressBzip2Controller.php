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

class CompressBzip2Controller extends CompressManagerFactoryAbstract implements CompressInterface
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
     * @param string $data
     * @param int $level
     * @return string
     */
    public function compressString(string $data, int $level = 9): string
    {
        return bzcompress($data, $level);
    }

    /**
     * @param string $data
     * @return string
     */
    public function decompressString(string $data): string
    {
        return bzdecompress($data);
    }

    /**
     * @param string $filename
     * @param string $mode
     * @return boolean
     * @throws \Exception
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

    /**
     * @return string
     */
    function getFileExtension(): string
    {
        return 'bz2';
    }
}
