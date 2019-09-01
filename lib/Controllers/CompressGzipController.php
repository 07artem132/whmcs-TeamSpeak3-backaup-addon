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

class CompressGzipController extends CompressManagerFactoryAbstract implements CompressInterface
{
    private $fileHandler = null;

    /**
     * CompressGzip constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        if (!function_exists("gzopen")) {
            throw new \Exception("Compression is enabled, but gzip lib is not installed or configured properly");
        }
    }

    /**
     * @param string $data
     * @param int $level
     * @return string
     */
    public function compressString(string $data, int $level = 9): string
    {
        return gzencode($data, $level);
    }

    /**
     * @param string $data
     * @return string
     */
    public function decompressString(string $data): string
    {
        return gzdecode($data);
    }

    /**
     * @param string $filename
     * @param string $mode
     * @return boolean
     * @throws \Exception
     */
    public function open(string $filename, string $mode = 'wb9'): bool
    {
        $this->fileHandler = gzopen($filename, $mode);
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
    public function write(string $str): int
    {
        if (false === ($bytesWritten = gzwrite($this->fileHandler, $str))) {
            throw new \Exception("Writting to file failed! Probably, there is no more free space left?");
        }
        return $bytesWritten;
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        return gzclose($this->fileHandler);
    }

    /**
     * @return string
     */
    function getFileExtension(): string
    {
        return 'gz';
    }

}

