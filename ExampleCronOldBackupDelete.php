<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 31.08.19 16:28
 *
 */

use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\TeamSpeakBackaup\Controllers\BackupController;
use WHMCS\Module\Addon\TeamSpeak3\Controllers\TeamSpeak3Controller;

require __DIR__ . '/../../../init.php';

/////////////////////////////////////////////////
/////           ЭТО ПРИМЕР КРОНА            /////
/////////////////////////////////////////////////

$backupController = new BackupController();

$listIDInstances = collect(Capsule::table('tblservers')
    ->where('type', 'teamspeak3')
    ->where('disabled', 0)->get())->keyBy('id')->keys();

foreach ($listIDInstances as $id) {
    try {
        $ts3 = new TeamSpeak3Controller($id);
        foreach ($ts3->getOnlineServerList() as $server) {
            try {
                //Функция принимает в качестве server обьект типа TeamSpeak3_Node_Server
                $backupController->removeOldBackup($server, false, true);
                echo 'remove old backup done->' . (string)$server->virtualserver_unique_identifier . PHP_EOL;
            } catch (\Throwable $e) {
                echo $e->getMessage();
            }
        }
    } catch (\Throwable $e) {
        echo 'error->' . $e->getMessage();
    }
}
