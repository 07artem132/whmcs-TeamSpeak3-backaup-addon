<?php
/**
 * Created by PhpStorm.
 * User: Artem
 * Date: 11.03.2018
 * Time: 14:04
 */


use WHMCS\Database\Capsule;

require __DIR__ . '/../../../init.php';

use ArgentCrusade\Selectel\CloudStorage\Api\ApiClient;
use ArgentCrusade\Selectel\CloudStorage\CloudStorage;

include __DIR__ . '/vendor/autoload.php';

exec( 'ulimit -n 4096' );

$moduleConfig = Capsule::table( 'tbladdonmodules' )->where( 'module', '=', 'TeamSpeakBackaup' )->get();

foreach ( $moduleConfig as $key => $item ) {
	$moduleConfig[ $item->setting ] = $item->value;
	unset( $moduleConfig[ $key ] );
}

$apiClient = new ApiClient( $moduleConfig['login'], $moduleConfig['password'] );
$storage   = new CloudStorage( $apiClient );

$containers = $storage->containers();
if ( $containers->has( $moduleConfig['container'] ) ) {
	$container = $containers->get( $moduleConfig['container'] );
	foreach ( Capsule::table( 'tblservers' )->get() as $server ) {
		if ( $server->type == 'teamspeak3' ) {
			try {

				$ts3_ServerInstance = TeamSpeak3::factory( "serverquery://$server->username:" . decrypt( $server->password ) . "@$server->ipaddress/?#use_offline_as_virtual" );
				echo 'server_ip->' . explode( ':', $server->ipaddress )[0] . PHP_EOL;
				foreach ( $ts3_ServerInstance as $ts3_VirtualServer ) {
					if ( $ts3_VirtualServer->isOnline() ) {
						echo 'server_port->' . $ts3_VirtualServer['virtualserver_port'] . PHP_EOL;
						$snapshot = $ts3_VirtualServer->snapshotCreate();
						$container->uploadFromString( '/' . explode( ':', $server->ipaddress )[0] . '/' . $ts3_VirtualServer['virtualserver_port'] . '/' . date( "Y-m-d_G" ) . '/snapshot.txt', $snapshot, [ 'deleteAt' => strtotime( '+10 day' ) ] );
						echo 'file_upload->' . 'snapshot.txt' . PHP_EOL;
						try {
							foreach ( $ts3_VirtualServer->channelFileList( 0, 0, "/icons" ) as $key => $value ) {
								if ( $value['size'] === 0 ) {
									continue;
								}
								$download = $ts3_VirtualServer->transferInitDownload( rand( 0x0000, 0xFFFF ), 0, (string) $value['src'] );
								$transfer = TeamSpeak3::factory( "filetransfer://" . ( strstr( $download["host"], ":" ) !== false ? "[" . $download["host"] . "]" : $download["host"] ) . ":" . $download["port"] );
								$Image    = $transfer->download( $download["ftkey"], $download["size"] );
								$container->uploadFromString( '/' . explode( ':', $server->ipaddress )[0] . '/' . $ts3_VirtualServer['virtualserver_port'] . '/' . date( "Y-m-d_G" ) . '/' . (string) $value['name'] . image_type_to_extension( getimagesizefromstring( $Image )['2'] ), $Image, [ 'deleteAt' => strtotime( '+10 day' ) ] );
								echo 'file_upload->' . (string) $value['name'] . PHP_EOL;
							}
						} catch ( \Exception $e ) {
							echo $e->getMessage();
						}
					}
				}
			} catch ( \Exception $e ) {
				echo $e->getMessage();
			}
		}
	}
}
