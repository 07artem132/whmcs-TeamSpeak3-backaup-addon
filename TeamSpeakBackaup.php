<?php
/**
 * Created by PhpStorm.
 * User: Artem
 * Date: 11.03.2018
 * Time: 12:29
 */

use WHMCS\Module\Addon\TeamSpeakBackaup\Configs\ModuleConfig;


function TeamSpeakBackaup_config() {
	$configarray = [
		"name"        => "TeamSpeak backaup FTP",
		"description" => "",
		"version"     => "2",
		"author"      => "service-voice",
		"fields"      => [
            'note1' => [
                "FriendlyName" => "Данные для выгрузки",
            ],
            "FtpIp" => [
                "FriendlyName" => "IP FTP сервера",
                "Type" => "text",
                "Description" => "",
            ],
            "FtpPort" => [
                "FriendlyName" => "Порт FTP сервера",
                "Type" => "text",
                "Description" => "",
            ],
            "FtpLogin" => [
                "FriendlyName" => "Логин FTP сервера",
                "Type" => "text",
                "Description" => "",
            ],
            "FtpPassword" => [
                "FriendlyName" => "Пароль FTP сервера",
                "Type" => "password",
                "Description" => "",
            ],
            "FtpPath" => [
                "FriendlyName" => "Путь на FTP сервере для бекапов",
                "Type" => "text",
                "Description" => "",
            ]
		]
	];

	return $configarray;
}

function TeamSpeakBackaup_output( $var ) {
	/*$config = new Config();
	$sftp   = new SFTP( $config['ip'] );
	if ( ! $sftp->login( $config['login'], $config['password'] ) ) {
		exit( 'Login Failed' );
	}

	$sftp->chdir( $config['path'] );

	if ( isset( $_GET['path'] ) && ! empty( $_GET['path'] ) ) {
		$sftp->chdir( base64_decode( $_GET['path'] ) );
		$config['path'] = base64_decode( $_GET['path'] );
	} elseif ( isset( $_GET['download'] ) && ! empty( $_GET['download'] ) ) {
		$pathInfo = explode( '/', base64_decode( $_GET['download'] ) );
		$fileName = $pathInfo[ count( $pathInfo ) - 2 ] . ':' . explode( '.', $pathInfo[ count( $pathInfo ) - 1 ] )[0] . '_' . $pathInfo[ count( $pathInfo ) - 3 ];
		$file     = $sftp->get( base64_decode( $_GET['download'] ) );
		header( "Cache-control: private" );
		header( "Content-type: application/zip" );
		header( "Content-Length: " . strlen( $file ) );
		header( "Content-Disposition: filename=$fileName.zip" );
		echo $file;
		die();
	}
	$paths = $sftp->nlist();

	foreach ( $paths as $path ) {
		if ( $path == '.' || $path == '..' ) {
			continue;
		}
		if ( preg_match( '/^.*\.(zip)$/i', $path ) ) {
			echo '<a href="addonmodules.php?module=TeamSpeakBackaup&download=' . base64_encode( $config['path'] . $path ) . '">Скачать ' . $path . '<a><br/>';
		} else {
			echo '<a href="addonmodules.php?module=TeamSpeakBackaup&path=' . base64_encode( $config['path'] . $path . '/' ) . '">' . $path . '<a><br/>';
		}
	}*/
}