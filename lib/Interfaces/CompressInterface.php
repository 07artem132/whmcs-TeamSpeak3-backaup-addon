<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 01.09.19 2:03
 *
 */

namespace WHMCS\Module\Addon\TeamSpeakBackaup\Interfaces;

interface CompressInterface
{
    /**
     * @param string $filename
     * @param string $mode
     */
    public function open(string $filename, string $mode);

    /**
     * @param string $data
     * @param int $level
     * @return string
     */
    public function compressString(string $data, int $level = 9): string;

    /**
     * @param string $data
     * @return string
     */
    public function decompressString(string $data): string;

    /**
     * @param string $str
     * @return mixed
     */
    public function write(string $str);

    /**
     * @return mixed
     */
    public function close();

    /**
     * @return string
     */
    public function getFileExtension(): string;

}