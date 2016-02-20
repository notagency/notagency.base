<?
namespace Notagency\Components;

\CBitrixComponent::includeComponentClass('notagency:materials.list');

class MaterialsDetail extends MaterialsList
{
    protected $checkParams = [
        'IBLOCK_CODE' => ['type' => 'string'],
    ];

    public function onPrepareComponentParams($arParams)
    {
        $arParams = parent::onPrepareComponentParams($arParams);
        $arParams['ELEMENTS_COUNT'] = 1;
        $arParams['PAGING'] = 'N';
        if (array_key_exists($arParams['REQUST_ELEMENT_CODE'], $_REQUEST) && !empty($_REQUEST[$arParams['REQUST_ELEMENT_CODE']]))
        {
            $arParams['ELEMENT_CODE'] = htmlspecialchars(trim($_REQUEST[$arParams['REQUST_ELEMENT_CODE']]));
        }
        if (array_key_exists($arParams['REQUST_ELEMENT_ID'], $_REQUEST) && intval($_REQUEST[$arParams['REQUST_ELEMENT_ID']]))
        {            
            $arParams['ELEMENT_ID'] = intval($_REQUEST[$arParams['REQUST_ELEMENT_ID']]);
        }
        return $arParams;
    }

    protected function executeMain()
    {
        $filterInitialized = false;
        if ($this->arParams['ELEMENT_ID'])
        {
            $this->elementsFilter['ID'] = $this->arParams['ELEMENT_ID'];
            $filterInitialized = true;
        }
        if ($this->arParams['ELEMENT_CODE'])
        {
            $this->elementsFilter['CODE'] = $this->arParams['ELEMENT_CODE'];
            $filterInitialized = true;
        }
        if ($filterInitialized)
        {
            parent::executeMain();
            $this->arResult['ELEMENT'] = $this->arResult['ELEMENTS'][0];
        }
        unset($this->arResult['ELEMENTS']);
    }

    protected function executeEpilog()
    {
        global $APPLICATION;

        $includeIntoChain = $this->arParams['INCLUDE_INTO_CHAIN'];
        
        $chainFieldNames = explode('.', $this->arParams['INCLUDE_FIELD_INTO_CHAIN']);
        $chainPropertyNames = explode('.', $this->arParams['INCLUDE_PROPERTY_INTO_CHAIN']);

        $chainEntity = '';
        switch ($includeIntoChain)
        {
            case 'FIELD':
                foreach ($chainFieldNames as $chainFieldName)
                {
                    if (!array_key_exists($chainFieldName, $this->arResult['ELEMENT']))
                    {
                        continue;
                    }
                    if ($chainFieldName == 'DATE_ACTIVE_FROM' && $this->arResult['ELEMENT']['DISPLAY_ACTIVE_FROM'])
                    {
                        $chainEntity .= ' ' . $this->arResult['ELEMENT']['DISPLAY_ACTIVE_FROM'];
                    }
                    else
                    {
                        $chainEntity .= ' ' . $this->arResult['ELEMENT'][$chainFieldName];
                    }
                }
                break;
            case 'PROPERTY':
                foreach ($chainPropertyNames as $chainPropertyName)
                {
                    if (!array_key_exists($chainPropertyName, $this->arResult['ELEMENT']['PROPERTIES']))
                    {
                        continue;
                    }
                    if (empty($this->arResult['ELEMENT']['PROPERTIES'][$chainPropertyName]['VALUE']))
                    {
                        continue;
                    }


                    $chainEntity .= ' ' . $this->arResult['ELEMENT']['PROPERTIES'][$chainPropertyName]['VALUE'];
                }
                break;
        }
        if (!empty($chainEntity))
        {
            $APPLICATION->AddChainItem(trim($chainEntity));
        }
    }
    
    public function showResult()
    {
        if ($is404 = empty($this->arResult['ELEMENT']))
        {
            $this->abortCache();
            if ($this->arParams['SET_STATUS_404'] === 'Y')
            {
                $this->return404();
            }
            if ($this->arParams['SHOW_404'] === 'Y')
            {
                global $APPLICATION;
                $APPLICATION->RestartBuffer();
                require \Bitrix\Main\Application::getDocumentRoot() . SITE_TEMPLATE_PATH . '/header.php';
                require \Bitrix\Main\Application::getDocumentRoot() . '/404.php';
            }
            else
            {
                parent::showResult();
            }
        }
        else
        {
            parent::showResult();

        }
    }
}