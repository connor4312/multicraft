<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle = Yii::app()->name . ' - '.Yii::t('mc', 'User List');

$this->breadcrumbs=array(
    Yii::t('mc', 'Users'),
);

$this->menu=array(
    array(
        'label'=>Yii::t('mc', 'My Profile'),
        'url'=>array('user/view', 'id'=>Yii::app()->user->id),
        'icon'=>'user',
    ),
);
if (Yii::app()->user->isSuperuser())
{
    $this->menu[] =  array(
        'label'=>Yii::t('mc', 'Create User'),
        'url'=>array('create'),
        'icon'=>'user_new',
    );
}
?>

<?php $this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'user-grid',
    'dataProvider'=>$model->search(),
    'filter'=>$model,
    'columns'=>array(
        array('name'=>'name', 'type'=>'raw',
            'value'=>'CHtml::link(CHtml::encode($data->name), array("user/view", "id"=>$data->id))'),
        array('name'=>'email', 'visible'=>Yii::app()->user->isSuperuser()),
        array('name'=>'global_role', 'value'=>'($data->name == Yii::app()->user->superuser ? "'.Yii::t('mc', 'Root Superuser').'"'
            .' : User::getRoleLabel($data->global_role))', 'visible'=>Yii::app()->user->isSuperuser()),
        array('name'=>'lang', 'visible'=>Yii::app()->user->isSuperuser(), 'htmlOptions'=>array('width'=>20)),
    ),
)); ?>

