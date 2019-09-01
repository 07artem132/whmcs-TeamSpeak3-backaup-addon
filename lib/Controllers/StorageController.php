<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 26.08.19 21:39
 *
 */

namespace WHMCS\Module\Addon\TeamSpeakBackaup\Controllers;

use WHMCS\Module\Addon\TeamSpeakBackaup\Abstracts\CompressManagerFactoryAbstract;
use WHMCS\Module\Addon\TeamSpeakBackaup\Abstracts\CompressMethodAbstract;
use WHMCS\Module\Addon\TeamSpeakBackaup\Exceptions\FtpException;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Exception;

class StorageController
{
    private $ftp;

    /**
     * StorageController constructor.
     * @throws \WHMCS\Module\Addon\TeamSpeakBackaup\Exceptions\FtpException
     */
    function __construct()
    {
        $this->ftp = new FtpClientController();
        $this->ftp->connect();
        $this->ftp->login();

        if (!$this->ftp->isDir('icons')) {
            $this->ftp->mkdir('icons');
        }

        if (!$this->ftp->isDir('backups')) {
            $this->ftp->mkdir('backups');
        }

        if (!array_search('icons.json', $this->ftp->nlist('.', false))) {
            $this->ftp->putFromString('icons.json', '{}');
        }

    }

    /**
     * @param int $deps
     * @throws FtpException
     */
    private function reconnect(int $deps = 10)
    {
        try {
            $this->ftp->close();
            $this->ftp->connect();
            $this->ftp->login();
        } catch (FtpException $e) {
            echo 'error->' . $e->getMessage();
            if ($deps-- == 0)
                throw $e;
            sleep(1);
            $this->reconnect($deps);
        }
    }

    /**
     * @return string
     */
    public function getIconCacheFile(): string
    {
        return $this->ftp->getContent('/icons.json');
    }

    /**
     * @param array $cacheList
     * @param int $deps
     * @throws FtpException
     */
    public function putIconCacheFile(array $cacheList, int $deps = 10): void
    {
        try {
            $this->ftp->putFromString('/icons.json', json_encode($cacheList));
        } catch (FtpException $e) {
            echo 'error->' . $e->getMessage();
            if ($deps-- == 0)
                throw $e;
            $this->reconnect(10);
            $this->putIconCacheFile($cacheList, $deps);
        }
    }

    /**
     * @param string $icon
     * @param int $deps
     * @throws FtpException
     */
    public function putIcon(string $icon, int $deps = 10): void
    {
        try {
            $this->ftp->putFromString('/icons/' . crc32($icon), $icon);
        } catch (FtpException $e) {
            echo 'error->' . $e->getMessage() . PHP_EOL;
            if ($deps-- == 0)
                throw $e;
            $this->reconnect(10);
            $this->putIcon($icon, $deps);
        }
    }

    /**
     * @param string $uid
     * @param string $backup
     * @param string $tag
     * @param int $keepDays
     * @param string $compressMethod
     * @param int $deps
     * @throws FtpException
     * @throws Exception
     */
    public function putBackup(string $uid, string $backup, string $tag, int $keepDays, string $compressMethod, int $deps = 10): void
    {
        $path = '/backups/' . base64_encode($uid) . '/' . $tag;
        $date = date('Y-m-d_H-i');
        $compressManager = CompressManagerFactoryAbstract::create($compressMethod);

        if (!$this->ftp->isDir($path)) {
            $this->ftp->mkdir($path, true);
        }

        try {
            $this->ftp->putFromString(
                $path . '/' . $date . '.json.' . $compressManager->getFileExtension(),
                $compressManager->compressString($backup)
            );
            $this->ftp->putFromString($path . '/' . $date . '.json.keep', $keepDays);
        } catch (FtpException $e) {
            echo 'error->' . $e->getMessage() . PHP_EOL;
            if ($deps-- == 0)
                throw $e;
            $this->reconnect(10);
            $this->putBackup($uid, $backup, $tag, $keepDays, $deps);
        }
    }

    /**
     * @param string $uid
     * @param string $tag
     * @param string $backupDate
     * @param int $deps
     * @return string
     * @throws Exception
     */
    public function getBackup(string $uid, string $tag, string $backupDate, int $deps = 10): string
    {
        $path = '/backups/' . base64_encode($uid) . '/' . $tag . '/';
        try {
            $fileList = collect($this->ftp->nlist($path));

            $fileList = $fileList->filter(function ($fileName) use ($backupDate) {
                return strpos($fileName, $backupDate) !== false
                    && strpos($fileName, 'keep') == false;
            });

            $fileExtension = pathinfo($fileList->first(), PATHINFO_EXTENSION);

            $backup = $this->ftp->getContent($fileList->first());

            if (CompressMethodAbstract::isValidExtension($fileExtension)) {
                $compressManager = CompressManagerFactoryAbstract::create(
                    CompressMethodAbstract::getMethodForExtension($fileExtension)
                );
                $backup = $compressManager->decompressString($backup);
            }

            return $backup;
        } catch (Exception $e) {
            echo 'error->' . $e->getMessage() . PHP_EOL;
            if ($deps-- == 0)
                throw $e;
            $this->reconnect(10);
            return $this->getBackup($uid, $tag, $backupDate, $deps);
        }
    }

    /**
     * @param int $crc32
     * @param int $deps
     * @return string
     * @throws Exception
     */
    public function getIcon(int $crc32, int $deps = 10): string
    {
        try {
            return $this->ftp->getContent('/icons/' . $crc32);
        } catch (Exception $e) {
            echo 'error->' . $e->getMessage() . PHP_EOL;
            if ($deps-- == 0)
                throw $e;
            $this->reconnect(10);
            return $this->getIcon($crc32, $deps);
        }
    }

    /**
     * @param $uid
     * @return Collection
     * @throws FtpException
     */
    public function getBackupList(string $uid): Collection
    {
        $path = '/backups/' . base64_encode($uid);
        $backupList = collect();

        $ListPrefix = collect($this->ftp->nlist($path, false));

        $ListPrefix->each(function (string $pathPrefix) use ($path, $backupList) {
            $prefix = str_replace($path . '/', '', $pathPrefix);
            $backupList->put($prefix, collect());
        });

        $backupList->transform(function (Collection $list, string $prefix) use ($path) {
            $fileList = collect($this->ftp->nlist($path . '/' . $prefix));

            return collect($fileList->filter(function (string $value) {
                return strpos($value, '.json.gz') !== false;
            })->transform(function ($backupPath) use ($path, $prefix) {
                return Carbon::createFromFormat('Y-m-d_H-i', substr(
                    str_replace($path . '/' . $prefix . '/', '', $backupPath),
                    0,
                    -8
                ));
            })->values());
        });

        return $backupList;
    }

    /**
     * @param string $uid
     * @throws FtpException
     */
    public function removeEmptyDirTag(string $uid): void
    {
        $path = '/backups/' . base64_encode($uid);

        $ListPrefix = collect($this->ftp->nlist($path, false));

        $ListPrefix->each(function (string $pathPrefix) {
            if ($this->ftp->count($pathPrefix) === 0) {
                $this->ftp->rmdir($pathPrefix);
            }
        });
    }

    /**
     * @param string $uid
     * @param string $tag
     * @param string $backupDate
     * @param int $deps
     * @return Carbon
     * @throws FtpException
     * @throws Exception
     */
    public function getExpireBackupInfo(string $uid, string $tag, string $backupDate, int $deps = 10): Carbon
    {
        try {
            $path = '/backups/' . base64_encode($uid) . '/' . $tag . '/' . $backupDate . '.json.keep';

            $keepDay = (int)$this->ftp->getContent($path);

            return Carbon::createFromFormat('Y-m-d_H-i', $backupDate)->addDays($keepDay);
        } catch (\Exception $e) {
            echo 'error->' . $e->getMessage() . PHP_EOL;
            if ($deps-- == 0)
                throw $e;
            $this->reconnect(10);
            return $this->getExpireBackupInfo($uid, $tag, $backupDate, $deps);
        }
    }

    /**
     * @param string $uid
     * @param string $tag
     * @param string $backupDate
     * @param int $deps
     * @throws FtpException
     * @throws Exception
     */
    public function deleteBackup(string $uid, string $tag, string $backupDate, $deps = 10): void
    {
        try {
            $pathKeep = '/backups/' . base64_encode($uid) . '/' . $tag . '/' . $backupDate . '.json.keep';
            $pathBackup = '/backups/' . base64_encode($uid) . '/' . $tag . '/' . $backupDate . '.json.gz';

            $this->ftp->delete($pathKeep);
            $this->ftp->delete($pathBackup);
        } catch (Exception $e) {
            echo 'error->' . $e->getMessage() . PHP_EOL;
            if ($deps-- == 0)
                throw $e;
            $this->reconnect(10);
            $this->deleteBackup($uid, $tag, $backupDate, $deps);
        }
    }
}