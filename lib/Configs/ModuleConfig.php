<?php
/**
 *  Created by PhpStorm.
 *  User: Артём
 *  Date time: 26.07.19 20:57
 *
 */

namespace WHMCS\Module\Addon\TeamSpeakBackaup\Configs;
use WHMCS\Module\Addon\Setting;

class ModuleConfig
{
    private static $defaultLanguage = 'russian';
    private static $whmcsRootDir = ROOTDIR;
    private static $moduleName = 'TeamSpeakBackaup';

    public static function getTempPath()
    {
        return self::getWhmcsRootDir() . '/modules/addons/' . self::getModuleName() . '/temp';
    }

    /**
     * @return mixed
     */
    public static function getWhmcsRootDir()
    {
        return self::$whmcsRootDir;
    }

    /**
     * @return string
     */
    public static function getDefaultLanguage()
    {
        return self::$defaultLanguage;
    }

    /**
     * @return string
     */
    public static function getModuleName()
    {
        return self::$moduleName;
    }

    public static function getModuleLink()
    {
        global $module, $customadminpath;

        return '/' . $customadminpath . '/addonmodules.php?module=' . $module;
    }

    public static function getBaseFullPath()
    {
        return self::getWhmcsRootDir() . '/modules/addons/' . self::getModuleName();
    }

    public static function getBaseRelativePath()
    {
        return '/modules/addons/' . self::getModuleName();
    }
    public static function getModuleSetting($setting)
    {
        return Setting::Module(self::getModuleName())
            ->where('setting', '=', $setting)
            ->first()->value;
    }
}