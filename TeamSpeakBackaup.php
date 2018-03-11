<?php
/**
 * Created by PhpStorm.
 * User: Artem
 * Date: 11.03.2018
 * Time: 12:29
 */
error_reporting( - 1 );
ini_set( "display_errors", 1 );

require __DIR__ . '/vendor/autoload.php';

use ArgentCrusade\Selectel\CloudStorage\Api\ApiClient;
use ArgentCrusade\Selectel\CloudStorage\CloudStorage;
use WHMCS\Database\Capsule;

function TeamSpeakBackaup_config() {
	$configarray = [
		"name"        => "TeamSpeak backaup selectel",
		"description" => "",
		"version"     => "0.1",
		"author"      => "service-voice",
		"fields"      => [
			'login'     => array(
				'Type'         => 'text',
				'Size'         => '30',
				'Default'      => '',
				'Description'  => '',
				'FriendlyName' => 'Логин'
			),
			'password'  => array(
				'Type'         => 'text',
				'Size'         => '30',
				'Default'      => '',
				'Description'  => '',
				'FriendlyName' => 'Пароль'
			),
			'container' => array(
				'Type'         => 'text',
				'Size'         => '30',
				'Default'      => '',
				'Description'  => '',
				'FriendlyName' => 'Имя контейнера'
			),

		]
	];

	return $configarray;
}

function TeamSpeakBackaup_output( $vars ) {
	$apiClient  = new ApiClient( $vars['login'], $vars['password'] );
	$storage    = new CloudStorage( $apiClient );
	$containers = $storage->containers();
	if ( $containers->has( $vars['container'] ) ) {
		$container = $containers->get( $vars['container'] );
		echo 'ok';
	}
}