<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 30.08.19 16:25
 *
 */

namespace WHMCS\Module\Addon\TeamSpeakBackaup\Controllers;

use Carbon\Carbon;
use TeamSpeak3_Node_Server;
use TeamSpeak3;
use TeamSpeak3_Adapter_FileTransfer;
use Illuminate\Support\Collection;
use TeamSpeak3_Transport_Exception;

class BackupController
{
    /**
     * @var StorageController
     */
    private $storageController;
    /**
     * @var int[]
     */
    private $iconCacheFile;

    function __construct()
    {
        $this->storageController = new StorageController();
        $this->iconCacheFile = json_decode($this->storageController->getIconCacheFile(), true, JSON_THROW_ON_ERROR);
    }

    /**
     * @param TeamSpeak3_Node_Server $server
     * @param int $keepDays
     * @param string $tag
     * @throws \Exception
     */
    function createBackup(TeamSpeak3_Node_Server $server, int $keepDays, string $tag)
    {
        $uid = (string)$server->virtualserver_unique_identifier;
        $iconList = $this->getIconListFromServer($server);
        echo 'Icon list download from->' . $uid. PHP_EOL;
        $diff = $iconList->keys()->diff($this->iconCacheFile);

        if (!$diff->isEmpty()) {
            $downloadIcons = $this->getIconsFromServer($server, $diff);
            echo 'Icons download from server->' . $uid . PHP_EOL;
            foreach ($downloadIcons as $crc => $icon) {
                $this->storageController->putIcon($icon);
                $this->iconCacheFile[] = $crc;
                echo 'put ftp server icon->' . $crc . PHP_EOL;
            }
            $this->storageController->putIconCacheFile($this->iconCacheFile);
            echo 'put icon cache file->' . $crc . PHP_EOL;
        }

        $snapshot = $server->snapshotCreate(TeamSpeak3::SNAPSHOT_HEXDEC);
        echo 'snapshot create->' . $uid . PHP_EOL;
        $icons = $iconList->keys()->toArray();

        $backup = $this->buildBackup($uid, $snapshot, $icons);

        $this->storageController->putBackup($uid, $backup, $tag, $keepDays);
    }

    function getBackup(string $uid, string $tag, string $backupDate)
    {

    }

    function getIcon($crc32)
    {

    }

    /**
     * @param string $uid
     * @param string $snapshot
     * @param array $icons
     * @return string
     */
    private function buildBackup(string $uid, string $snapshot, array $icons): string
    {
        return CompressGzipController::compressString(json_encode([
            'uid' => $uid,
            'snapshot' => $snapshot,
            'icons' => $icons,
            'created_at' => time()
        ]), 9);
    }

    /**
     * @param TeamSpeak3_Node_Server $server
     * @param $iconIdList
     * @return string[]
     * @throws \TeamSpeak3_Adapter_FileTransfer_Exception
     * @throws \TeamSpeak3_Adapter_ServerQuery_Exception
     */
    private function getIconsFromServer(TeamSpeak3_Node_Server $server, Collection $iconIdList): Collection
    {
        /**
         * @var $transfer TeamSpeak3_Adapter_FileTransfer
         */
        $transfer = null;
        $iconIdList = $iconIdList->flip();
        $iconIdList->transform(function ($icon, $crc) use ($server, $transfer) {
            $download = $server->transferInitDownload(rand(0x0000, 0xFFFF), 0, '/icon_' . $crc);

            if ($transfer === null) {
                $transfer = TeamSpeak3::factory("filetransfer://" . $download["host"] . ":" . $download["port"]);
            }

            try {
                return $transfer->download($download["ftkey"], $download["size"])->toString();
            } catch (TeamSpeak3_Transport_Exception $e) {
                echo 'error->' . $e->getMessage() . PHP_EOL;
                sleep(5);
                $transfer->syn();
                return $transfer->download($download["ftkey"], $download["size"])->toString();
            }
        });

        return $iconIdList;
    }

    /**
     * @param TeamSpeak3_Node_Server $server
     * @return Collection
     * @throws \Exception
     */
    private function getIconListFromServer(TeamSpeak3_Node_Server $server): Collection
    {
        try {
            $iconList = collect($server->channelFileList(0, '', '/icons'))->keyBy(function ($item) {
                return substr($item['name'], 5);
            });

            return $iconList;
        } catch (\Exception $e) {
            if ($e->getMessage() != 'database empty result set') {
                throw  $e;
            }
            echo 'error->' . $e->getMessage() . PHP_EOL;
        }
        return collect([]);
    }

    /**
     * @param TeamSpeak3_Node_Server $server
     * @param bool $removeLastBackup
     */
    function removeOldBackup(TeamSpeak3_Node_Server $server, $removeLastBackup = false, $removeEmptyDir = true): void
    {
        $uid = (string)$server->virtualserver_unique_identifier;

        if ($removeEmptyDir) {
            $this->storageController->removeEmptyDirTag($uid);
        }

        $rawBackupList = $this->storageController->getBackupList($uid);

        $backupList = collect([]);
        foreach ($rawBackupList as $tag => $backupDates) {
            foreach ($backupDates as $backupDate) {
                $backupList->push(collect([
                    'create_at' => $backupDate,
                    'uid' => $uid,
                    'tag' => $tag,
                    'backupDate' => $backupDate->format('Y-m-d_H-i')
                ]));
            }
        }
        if ($backupList->count() == 1 && !$removeLastBackup) {
            return;
        }

        if (!$removeLastBackup) {
            $backupList = $backupList->sortBy('create_at')->take($backupList->count() - 1);
        }

        $diffDate = Carbon::now();

        $backupList->each(function (Collection $backupInfo) use ($diffDate) {
            $expire_at = $this->storageController->getExpireBackupInfo(
                $backupInfo->get('uid'),
                $backupInfo->get('tag'),
                $backupInfo->get('backupDate'),
                );
            if ($expire_at->diffInSeconds($diffDate, false) > 0) {
                $this->storageController->deleteBackup(
                    $backupInfo->get('uid'),
                    $backupInfo->get('tag'),
                    $backupInfo->get('backupDate'),
                    );
            }
        });
    }
}