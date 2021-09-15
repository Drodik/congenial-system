<?php

class versionOneComponent extends \CBitrixComponent
{
    public function executeComponent()
    {
        $arResult = &$this->arResult;
        $arParams = &$this->arParams;

        $arDefaultUrlTemplates404 = [
            'error' => 'index.php',
            'user' => 'user/#ACTION#',
        ];

        $arDefaultVariableAliases404 = [];

        $arComponentVariables = ['code'];

        $arVariables = [];

        $arUrlTemplates = \CComponentEngine::MakeComponentUrlTemplates(
            $arDefaultUrlTemplates404,
            $arParams['SEF_URL_TEMPLATES']
        );

        $arVariableAliases = \CComponentEngine::MakeComponentVariableAliases(
            $arDefaultVariableAliases404,
            $arParams['VARIABLE_ALIASES']
        );

        $componentPage = \CComponentEngine::ParseComponentPath(
            $arParams['SEF_FOLDER'],
            $arUrlTemplates,
            $arVariables
        );

        if ( !(is_string($componentPage)
            && isset($componentPage[0])
            && isset($arDefaultUrlTemplates404[$componentPage]))
        )
        {
            $componentPage = 'error';
        }

        \CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);

        foreach ($arUrlTemplates as $url => $value)
        {
            $key = 'PATH_TO_'.strtoupper($url);
            $arResult[$key] = isset($arParams[$key][0]) ? $arParams[$key] : $arParams['SEF_FOLDER'].$value;
        }

        $arResult =
            array_merge(
                [
                    'VARIABLES' => $arVariables,
                ],
                $arResult
            );

        $this->IncludeComponentTemplate($componentPage);
    }
}
