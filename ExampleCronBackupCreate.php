<?php
/**
 * Created by PhpStorm.
 * User: Artem
 * Date: 11.03.2018
 * Time: 14:04
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
                $backupController->createBackup($server, 7, 'auto');
                echo 'backup done->' . (string)$server->virtualserver_unique_identifier . PHP_EOL;
            } catch (\Throwable $e) {
                echo $e->getMessage();
            }
        }
    } catch (\Throwable $e) {
        echo 'error->' . $e->getMessage();
    }
}
