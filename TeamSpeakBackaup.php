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

}