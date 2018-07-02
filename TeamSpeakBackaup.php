<?php
/**
 * Created by PhpStorm.
 * User: Artem
 * Date: 11.03.2018
 * Time: 12:29
 */

use TeamSpeakBackaup\lib\Config;
use TeamSpeakBackaup\lib\FileManager;
use TeamSpeakBackaup\lib\Log;
use phpseclib\Net\SFTP;

require __DIR__ . '/vendor/autoload.php';

function TeamSpeakBackaup_config() {
	$configarray = [
		"name"        => "TeamSpeak backaup FTP",
		"description" => "",
		"version"     => "1",
		"author"      => "service-voice",
		"fields"      => [
			'ip'         => array(
				'Type'         => 'text',
				'Size'         => '30',
				'Default'      => '',
				'Description'  => '',
				'FriendlyName' => 'ip'
			),
			"type"       => [
				"FriendlyName" => "Тип соединения",
				"Type"         => "dropdown",
				"Options"      => "sftp",
				"Description"  => "",
				"Default"      => "sftp",
			],
			'login'      => array(
				'Type'         => 'text',
				'Size'         => '30',
				'Default'      => '',
				'Description'  => '',
				'FriendlyName' => 'Логин'
			),
			'password'   => array(
				'Type'         => 'password',
				'Size'         => '30',
				'Default'      => '',
				'Description'  => '',
				'FriendlyName' => 'Пароль'
			),
			'path'       => array(
				'Type'         => 'text',
				'Size'         => '30',
				'Default'      => '/root/',
				'Description'  => 'Обязательно со слешом на конце, папка должна сушествовать.',
				'FriendlyName' => 'Путь для сохранения бекапов'
			),
			"compressed" => [
				"FriendlyName" => "Тип сжатия",
				"Type"         => "dropdown",
				"Options"      =>".zip" ,
				"Description"  => "",
				"Default"      => ".zip",
			],
			"DeleteFor"  => [
				"FriendlyName" => "Сколько дней хранить резервные копии ?",
				"Type"         => "text",
				'Size'         => '30',
				"Description"  => "Вводить только цифры",
				"Default"      => "7",
			]

		]
	];

	return $configarray;
}

function TeamSpeakBackaup_output( $var ) {
	$config = new Config();
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
	}
}