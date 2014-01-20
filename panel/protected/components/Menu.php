<?php

Yii::import('zii.widgets.CMenu');

class Menu extends CMenu
{
    protected function renderMenuItem($item)
    {
        if (isset($item['icon']))
            $item['label'] = '<i class="fa fa-' . $item['icon'] . '"></i> ' . $item['label'];
        return parent::renderMenuItem($item);
    }
}



