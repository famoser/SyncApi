<?php
/**
 * Created by PhpStorm.
 * User: Florian Moser
 * Date: 05.11.2016
 * Time: 18:07
 */

namespace Famoser\SyncApi\Types;


/**
 * used to distinguish setting keys
 * 
 * @package Famoser\SyncApi\Types
 */
class SettingKeys
{
    const AUTHORIZATION_CODE_VALID_TIME = 0;
    const DEVICE_AUTHENTICATION_REQUIRED = 1;
    const AUTHORIZATION_CODE_LENGTH = 2;

    /**
     * get a description of a setting to be displayed to a user
     * 
     * @param $val
     * @return string
     */
    public static function getSettingDescription($val)
    {
        switch ($val) {
            case SettingKeys::AUTHORIZATION_CODE_VALID_TIME:
                return "authorization code valid time";
            case SettingKeys::DEVICE_AUTHENTICATION_REQUIRED:
                return "device authentication required";
            case SettingKeys::AUTHORIZATION_CODE_LENGTH:
                return "length of the authorization code";
            default:
                return "unknown setting";
        }
    }

    /**
     * get a default value of a setting
     * 
     * @param $val
     * @return string
     */
    public static function getDefaultValue($val)
    {
        switch ($val) {
            case SettingKeys::AUTHORIZATION_CODE_VALID_TIME:
                return "300";
            case SettingKeys::DEVICE_AUTHENTICATION_REQUIRED:
                return "false";
            case SettingKeys::AUTHORIZATION_CODE_LENGTH:
                return "6";
            default:
                return "unknown";
        }
    }

    /**
     * checks if the value to be assigned to a setting is valid
     * 
     * @param $val
     * @return bool
     */
    public static function isValidValue($val)
    {
        switch ($val) {
            case SettingKeys::AUTHORIZATION_CODE_VALID_TIME:
                return is_numeric($val);
            case SettingKeys::DEVICE_AUTHENTICATION_REQUIRED:
                return $val == "false" || $val == "true";
            case SettingKeys::AUTHORIZATION_CODE_LENGTH:
                return is_numeric($val);
            default:
                return false;
        }
    }
}
