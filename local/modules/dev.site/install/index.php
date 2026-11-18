<?php

class dev_site extends CModule
{
    public const MODULE_ID = 'dev.site';

    public $MODULE_ID = 'dev.site';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME = 'Тренировочный модуль';
    public $PARTNER_NAME = 'dev';

    public function __construct()
    {
        $arModuleVersion = array();
        include __DIR__ . '/version.php';

        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
    }

    public function InstallFiles($arParams = array())
    {
        return true;
    }

    public function UnInstallFiles()
    {
        return true;
    }

    public function DoInstall()
    {
        RegisterModule($this->MODULE_ID);

        $agentName = '\Dev\Site\Agents\Iblock::clearOldLogs();';
        $agent = \CAgent::GetList(
            [],
            [
                'NAME' => $agentName,
                'MODULE_ID' => $this->MODULE_ID
            ]
        )->Fetch();

        if (!$agent) {
            \CAgent::AddAgent(
                $agentName,
                $this->MODULE_ID,
                'Y',
                3600
            );
        }
        $this->InstallFiles();
    }

    public function DoUninstall()
    {
        $agentName = '\Dev\Site\Agents\Iblock::clearOldLogs();';
        $rsAgents = \CAgent::GetList(
            [],
            [
                'NAME' => $agentName,
                'MODULE_ID' => $this->MODULE_ID
            ]
        );
        while ($agent = $rsAgents->Fetch()) {
            \CAgent::Delete($agent['ID']);
        }
        UnRegisterModule($this->MODULE_ID);
        $this->UnInstallFiles();
    }
}
