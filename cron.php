<?php
/**
 * Created by PhpStorm.
 * User: Artem
 * Date: 11.03.2018
 * Time: 14:04
 */

use WHMCS\Database\Capsule;
use Alchemy\Zippy\Zippy;
use phpseclib\Net\SFTP;
use TeamSpeakBackaup\lib\Config;
use TeamSpeakBackaup\lib\FileManager;
use TeamSpeakBackaup\lib\Log;

require __DIR__ . '/../../../init.php';
require __DIR__ . '/vendor/autoload.php';

set_time_limit( 90000 );
ini_set( 'memory_limit', '2048M' );
ini_set( 'error_reporting', E_ALL );
ini_set( 'display_errors', 1 );
ini_set( 'display_startup_errors', 1 );
exec( 'ulimit -n 40096' );

$config           = new Config();
$zippy            = Zippy::load();
$backaupLocalPath = ROOTDIR . '/modules/addons/TeamSpeakBackaup/backaup/';
$sftp             = new SFTP( $config['ip'] );
Log::info( 'с сервером соединились' );
if ( ! $sftp->login( $config['login'], $config['password'] ) ) {
	exit( 'Login Failed' );
}
Log::info( 'Логин и пароль верный' );
$date = date( "d.m.Y_H:i" );
$sftp->chdir( $config['path'] );
$dir_list = $sftp->nlist( $config['path'] );

if ( array_search( $date, $dir_list ) === false ) {
	$sftp->mkdir( $date );
}
$config['path'] .= $date . '/';
$sftp->chdir( $date );


if ( ! file_exists( $backaupLocalPath ) ) {
	FileManager::create( $backaupLocalPath );
} else {
	FileManager::remove( $backaupLocalPath );
	FileManager::create( $backaupLocalPath );
}

Log::info( '-------------------------------' );
Log::info( 'Создание бекапов' );
Log::info( '-------------------------------' );

$servers = Capsule::table( 'tblservers' )->get();

foreach ( $servers as $server ) {
	if ( $server->type != 'teamspeak3' ) {
		continue;
	}

	try {
		list( $ip, $sqPort ) = explode( ':', $server->ipaddress );

		$ts3_ServerInstance = TeamSpeak3::factory( "serverquery://$server->username:" . decrypt( $server->password ) . "@$ip:$sqPort/?#use_offline_as_virtual&blocking=0" );

		if ( ! file_exists( $backaupLocalPath . $ip ) ) {
			FileManager::create( $backaupLocalPath . $ip );
		}

		Log::info( 'Начинаем обработку инстанса: ' . $ip );
		$sftp->mkdir( $ip );
		$sftp->chdir( $ip );
		foreach ( $ts3_ServerInstance as $ts3_VirtualServer ) {
			if ( ! $ts3_VirtualServer->isOnline() ) {
				continue;
			}

			Log::info( 'Виртуальный сервер с портом: ' . $ts3_VirtualServer['virtualserver_port'] );

			$virtualServerPath = $backaupLocalPath . $ip . '/' . $ts3_VirtualServer['virtualserver_port'];

			$archive = new ZipArchive();
			if ( $archive->open( $virtualServerPath . '.zip', ZipArchive::CREATE ) !== true ) {
				exit( "Невозможно открыть <$filename>\n" );
			}

			$archive->addFromString( 'snapshot', $ts3_VirtualServer->snapshotCreate() );

			//	Log::info( 'снапшот создан' );
			//	Log::info( 'Начинаем загрузку иконок' );
			if ( isset( $argv[1] ) && $argv[1] == '--icon' ) {
				try {
					foreach ( $ts3_VirtualServer->channelFileList( 0, 0, "/icons" ) as $key => $value ) {
						if ( $value['size'] === 0 ) {
							continue;
						}

						$download = $ts3_VirtualServer->transferInitDownload( rand( 0x0000, 0xFFFF ), 0, (string) $value['src'] );
						$transfer = TeamSpeak3::factory( "filetransfer://" . ( strstr( $download["host"], ":" ) !== false ? "[" . $download["host"] . "]" : $download["host"] ) . ":" . $download["port"] );
						$Image    = $transfer->download( $download["ftkey"], $download["size"] );
						$archive->addFromString( (string) $value['name'] . image_type_to_extension( getimagesizefromstring( $Image )['2'] ), $Image );
					}
					//		Log::info( 'Иконки скачаны' );

				} catch ( \Exception $e ) {
					Log::info( 'Во время скачивания иконок произошла ошибка: ' . $e->getMessage() );
				}
			}
			$archive->close();
			//	Log::info( 'Архив создан' );
			$sftp->put( $config['path'] . $ip . '/' . $ts3_VirtualServer['virtualserver_port'] . '.zip', $virtualServerPath . '.zip', SFTP::SOURCE_LOCAL_FILE );
			//	Log::info( 'Архив выгружен' );
			unlink( $virtualServerPath . '.zip' );
		}
		$sftp->chdir( '../' );
	} catch ( \Exception $e ) {
		echo $e->getMessage() . PHP_EOL;
	}
}

$sftp->chdir( '../' );
$time = time();
foreach ( $sftp->rawlist() as $item ) {
	if ( $item['filename'] == '.' || $item['filename'] == '..' ) {
		continue;
	}
	$timeAfter   = time() - $item['mtime'];
	$deleteAfter = (int) $config['DeleteFor'] * 86400;
	if ( $timeAfter > $deleteAfter ) {
		$sftp->delete( $item['filename'], true );
	}
}