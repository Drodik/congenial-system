<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

try{
    $APPLICATION->IncludeComponent("api:v1","",	[
        'SEF_MODE' => 'Y',
        "SEF_FOLDER" => '/api/v1/',
        "SEF_URL_TEMPLATES" => [
            'error' => 'index.php',
            'user' => 'user/#ACTION#',
        ]
    ],
        $component
    );

} catch (\Throwable $e) {
    ShowError($e->getMessage());
}