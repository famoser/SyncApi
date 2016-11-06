<?php
/**
 * Created by PhpStorm.
 * User: Florian Moser
 * Date: 05.11.2016
 * Time: 20:21
 */

namespace Famoser\SyncApi\Repositories;

/*
    const AuthorizationCodeValidTime = 0;
    const DeviceAuthenticationRequired = 1;
    const AuthorizationCodeLength = 2;
*/

use Famoser\SyncApi\Helpers\DatabaseHelper;
use Famoser\SyncApi\Models\Entities\ApplicationSetting;
use Famoser\SyncApi\Types\SettingKeys;

class SettingsRepository
{
    private $helper;
    private $applicationId;

    public function __construct(DatabaseHelper $helper, $applicationId)
    {
        $this->helper = $helper;
        $this->applicationId = $applicationId;
    }

    private $isInitialized;
    private $dic;

    private function ensureInitialized()
    {
        if ($this->isInitialized)
            return;

        $settings = $this->helper->getFromDatabase(new ApplicationSetting(), "application_id = :application_id", array("application_id" => $this->applicationId));
        foreach ($settings as $setting) {
            $this->dic[$setting->key] = $setting->val;
        }
        $this->isInitialized = true;
    }

    private function getOrCreateValue($val)
    {
        $this->ensureInitialized();
        if (in_array($val, $this->dic))
            return $this->dic[$val];
        $setting = new ApplicationSetting();
        $setting->application_id = $this->applicationId;
        $setting->key = $val;
        $setting->val = SettingKeys::getDefaultValue($val);
        $this->helper->saveToDatabase($setting);
        $this->dic[$setting->key] = $setting->val;
        return $setting->val;
    }

    public function getAuthorizationCodeValidTime()
    {
        return $this->getOrCreateValue(SettingKeys::AuthorizationCodeValidTime);
    }

    public function getDeviceAuthenticationRequired()
    {
        return $this->getOrCreateValue(SettingKeys::DeviceAuthenticationRequired);
    }

    public function getAuthorizationCodeLength()
    {
        return $this->getOrCreateValue(SettingKeys::AuthorizationCodeLength);
    }
}