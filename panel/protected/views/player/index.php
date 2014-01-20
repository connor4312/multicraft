<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle = Yii::app()->name . ' - '.Yii::t('mc', 'Player List');

$this->breadcrumbs=array(
    Yii::t('mc', 'Servers')=>array('server/index'),
    Server::model()->findByPk((int)$sv)->name=>array('server/view', 'id'=>$sv),
    Yii::t('mc', 'Players'),
);

$this->menu=array(
    array(
        'label'=>Yii::t('mc', 'Create Player'),
        'url'=>array('create', 'sv'=>$sv),
        'icon'=>'player_new',
    ),
    array(
        'label'=>Yii::t('mc', 'Manage Players'),
        'url'=>array('admin'),
        'visible'=>Yii::app()->user->isSuperuser(),
        'icon'=>'player',
    ),
    array(
        'label'=>Yii::t('mc', 'Back'),
        'url'=>array('server/view',
        'id'=>$sv),
        'icon'=>'back',
    ),
);
?>

<?php $this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'player-grid',
    'dataProvider'=>$model->search(),
    'filter'=>$model,
    'ajaxUpdate'=>false,
    'columns'=>array(
        array('name'=>'name', 'type'=>'raw',
            'value'=>'CHtml::link(CHtml::encode($data->name), array("player/view", "id"=>$data->id))'),
        array('name'=>'level','headerHtmlOptions'=>array('width'=>'90'),
            'value'=>'$data->level == 1 ? Yii::t("mc", "Default Role") : User::getRoleLabel(User::getLevelRole($data->level))'),
        array('name'=>'lastseen', 'value'=>'$data->lastseen ? @date("'.Yii::t('mc', 'd. M Y, H:i').'", (int)$data->lastseen) : "'.Yii::t('mc', 'Never').'"'),
        'ip',
    ),
)); ?>
