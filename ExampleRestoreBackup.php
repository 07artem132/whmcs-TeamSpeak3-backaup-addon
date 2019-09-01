<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 31.08.19 23:49
 *
 */


use WHMCS\Module\Addon\TeamSpeak3\Controllers\TeamSpeak3Controller;
use WHMCS\Module\Addon\TeamSpeakBackaup\Controllers\BackupController;

require __DIR__ . '/../../../init.php';

$backupController = new BackupController();
$backup = $backupController->getBackup(
    base64_decode('Zy9nOVZDYTBXNGRnc051dHQzRElkL2VROStjPQ=='),
    'auto',
    '2019-08-31_19-54',
    true
);

$ts3 = new TeamSpeak3Controller($id);
$server = $ts3->getConnection()->serverGetByPort(9987);
$server->snapshotDeploy($backup['snapshot'],TeamSpeak3::SNAPSHOT_HEXDEC);

$backup['icons'];