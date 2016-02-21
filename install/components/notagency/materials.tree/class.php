<?
namespace Notagency\Components;

if (!\Bitrix\Main\Loader::includeModule('materials.list')) return false;

class MaterialsTree extends MaterialsList
{
    /**
     * @inheritdoc
     */
    protected function executeMain()
    {
        parent::executeMain();
        $this->buildTree();
    }

    /**
     * Строит дерево разделов и элементов, результат в $arResult['TREE']
     */
    protected function buildTree()
    {
        if (!empty($this->arResult['SECTIONS']) && !empty($this->arResult['ELEMENTS']))
        {
            foreach ($this->arResult['SECTIONS'] as $section)
            {
                $this->arResult['TREE'][$section['ID']] = $section;
            }
            foreach ($this->arResult['ELEMENTS'] as $element)
            {
                if (intval($element['IBLOCK_SECTION_ID']) > 0)
                {
                    $this->arResult['TREE'][$element['IBLOCK_SECTION_ID']]['ELEMENTS'][] = $element;
                }
            }
        }
    }
}