<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

$this->pageTitle=Yii::app()->name . ' - '.Yii::t('admin', 'Multicraft Status');
$this->breadcrumbs=array(
    Yii::t('admin', 'Settings')=>array('index'),
    Yii::t('admin', 'Multicraft Status'),
);

$this->menu=array(
    array(
        'label'=>Yii::t('admin', 'Back'),
        'url'=>array('daemon/index'),
        'icon'=>'back',
    ),
);

echo Yii::t('admin', 'Your current panel version is {v}', array('{v}' => $this->version));
?>
<br/>

<?php $w = $this->widget('zii.widgets.CListView', array(
    'emptyText'=>Yii::t('admin', 'No daemons found.').'<br/><br/>'.Yii::t('admin', 'Please check that at least one daemon is started and that it uses the same database you configured as the daemon database using the control panel installer.'),
    'dataProvider'=>$daemonList,
    'itemView'=>'_statusBox',
    'loadingCssClass'=>'',
    'beforeAjaxUpdate'=>'function(id){ stopRefreshList(id); }',
    'afterAjaxUpdate'=>'function(id, data){ scheduleRefreshList(id, data); }',
)); ?>

<?php echo CHtml::script('
    var refreshTimer = 0;
    function stopRefreshList(id) {
        if (refreshTimer)
            clearTimeout(refreshTimer);
    }
    function scheduleRefreshList(id, data) {
        stopRefreshList(id);
        refreshTimer = setTimeout(function() {jQuery("#"+id).yiiListView.update(id);}, 2000);
    }
    $(document).ready(function() {
        scheduleRefreshList("'.$w->id.'", "");
    });
    ') ?>
