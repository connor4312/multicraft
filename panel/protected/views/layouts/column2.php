<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
?>
<?php $this->beginContent('//layouts/main'); ?>
<div class="col-md-3" id="sidebar">
    <?php
        $this->beginWidget('zii.widgets.CPortlet', array(
            'title'=>end($this->breadcrumbs),
            'hideOnEmpty'=>false,
        ));
        $this->widget('application.components.Menu', array(
            'items'=>$this->menu,
            'htmlOptions'=>array('class'=>'operations'),
        ));
        $this->endWidget();
    ?>
</div>
<div class="col-md-9 col-md-offset-3">

    <?php
        if (count($this->breadcrumbs) > 1) {
            $this->widget('zii.widgets.CBreadcrumbs', array(
                'homeLink'=>'',
                'links'=>$this->breadcrumbs,
                'tagName' => 'ol',
                'htmlOptions' => array('class' => 'breadcrumb'),
                'separator' => '',
                'activeLinkTemplate' => '<li><a href="{url}">{label}</a></li>',
                'inactiveLinkTemplate' => '<li class="active">{label}</li>',
            ));
        }
    ?>
    <?php echo $content; ?>
</div>
<?php $this->endContent(); ?>
