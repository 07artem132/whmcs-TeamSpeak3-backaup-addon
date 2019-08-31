<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 26.07.19 22:59
 *
 */

namespace WHMCS\Module\Addon\TeamSpeakBackaup\Controllers;

use WHMCS\Module\Addon\TeamSpeakBackaup\Abstracts\CompressManagerFactoryAbstract;

class CompressGzipController extends CompressManagerFactoryAbstract
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

    public static function compressString(string $data, int $level): string
    {
        return gzencode($data, $level);
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
}

