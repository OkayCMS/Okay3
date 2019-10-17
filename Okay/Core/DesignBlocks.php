<?php


namespace Okay\Core;


/**
 * Class DesignBlocks
 * @package Okay\Core
 * 
 * Класс для работы с блоками в админке. Чтобы зарегистрировать ещё один блок,
 * нужно добавить его здесь и добавить его вызов в соответствующем месте админки
 * 
 * Пример вызова:
    <div class="okay_block_wrap">
        {$block = {get_design_block block="product_variant"}}
        {if !empty($block) || $config->dev_mode}
            {if $config->dev_mode}
            <div class="block_name">product_variant</div>
            {/if}
            {$variant_block}
        {/if}
    </div>
 */

class DesignBlocks
{
    /**
     * @var array список зарегистрированных блоков
     */
    private $registeredBlocks;
    
    private $design;

    public function __construct(Design $design)
    {
        $this->design = $design;
    }

    /**
     * @param string $blockName название блока
     *
     * @param string $blockTplFile путь к .tpl файлу из модуля, который представляет верстку блока
     * @throws \Exception
     *
     * Регистрация блока для админки
     */
    public function registerBlock($blockName, $blockTplFile)
    {
        if (!is_file($blockTplFile)) {
            throw new \Exception('File ' . $blockTplFile . ' not found');
        }

        $this->registeredBlocks[$blockName][] = $blockTplFile;
    }

    /**
     * @param string $blockName
     * @return string
     * Метод компилирует и возвращает HTML блока
     */
    public function getBlockHtml($blockName)
    {
        $blockHtml = '';
        if (!empty($this->registeredBlocks[$blockName])) {
            // Разворачиваем, потому что модули регистрируются обратно тому,
            // как они расположены в админ панели, а их отображение должно соответствовать
            // порядку в админ панели
            $reversedBlocks = array_reverse($this->registeredBlocks[$blockName]);
            foreach ($reversedBlocks as $blockTplFile) {
                $blockHtml .= $this->design->fetch($blockTplFile);
            }
        }
        return $blockHtml;
    }

}
