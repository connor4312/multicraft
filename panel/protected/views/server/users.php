<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle = Yii::app()->name . ' - '.Yii::t('mc', 'Users');

$this->breadcrumbs=array(
    Yii::t('mc', 'Servers')=>array('index'),
    $model->name=>array('view', 'id'=>$model->id),
    Yii::t('mc', 'Users'),
);

$this->menu = array(
    array(
        'label'=>Yii::t('mc', 'Back'),
        'url'=>array('server/view', 'id'=>$model->id),
        'icon'=>'back',
    )
);
?>

<?php echo '<b>'.Yii::t('mc', 'Changes on this page take effect immediately.').'</b>' ?>

<?php

$cols = array('name');
if ($userFtp)
{
    $cols[] = array(
            'header'=>Yii::t('mc', 'FTP access / FTP username'),
            'class'=>'FtpAccessDropdownColumn',
        );
}
$cols[] = array(
        'header'=>Yii::t('mc', 'Role'),
        'class'=>'RoleDropdownColumn',
    );

$this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'users-grid',
    'dataProvider'=>$provider,
    'filter'=>$users,
    'ajaxUpdate'=>false,
    'columns'=>$cols,
)); ?>
