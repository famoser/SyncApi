<?php
/**
 * Created by PhpStorm.
 * User: Florian Moser
 * Date: 05.11.2016
 * Time: 18:07
 */

namespace Famoser\SyncApi\Types;


class SettingKeys
{
    const AuthorizationCodeValidTime = 0;
    const DeviceAuthenticationRequired = 1;

    public static function getSettingDescription($val)
    {
        switch ($val) {
            case SettingKeys::AuthorizationCodeValidTime:
                return "authorization code valid time";
            case SettingKeys::DeviceAuthenticationRequired:
                return "device authentication required";
            default:
                return "unknown setting";
        }
    }

    public static function getDefaultValue($val)
    {
        switch ($val) {
            case SettingKeys::AuthorizationCodeValidTime:
                return "300";
            case SettingKeys::DeviceAuthenticationRequired:
                return "false";
            default:
                return "unknown";
        }
    }

    public static function isValidValue($val)
    {
        switch ($val) {
            case SettingKeys::AuthorizationCodeValidTime:
                return is_numeric($val);
            case SettingKeys::DeviceAuthenticationRequired:
                return $val == "false" || $val == "true";
            default:
                return false;
        }
    }
}