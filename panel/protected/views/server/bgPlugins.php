<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle = Yii::app()->name . ' - '.Yii::t('mc', 'BukGet Plugin List').($installed ? ' - '.Yii::t('mc', 'Currently Installed') : '');

$this->breadcrumbs=array(
    Yii::t('mc', 'Servers')=>array('index'),
    $model->name=>array('view', 'id'=>$model->id),
    $installed ? Yii::t('mc', 'Currently Installed') : Yii::t('mc', 'BukGet Plugin List'),
);

$this->menu = array();
if (!$installed)
{
    $this->menu[] = array(
        'label'=>Yii::t('mc', 'Currently Installed'),
        'url'=>array('server/bgPlugins', 'id'=>$model->id, 'installed'=>true),
        'icon'=>'plugin',
    );
}
$this->menu[] = array(
    'label'=>Yii::t('mc', 'Back'),
    'url'=>array(($installed ? 'server/bgPlugins' : 'server/view'), 'id'=>$model->id),
    'icon'=>'back',
);

echo CHtml::beginForm('', 'GET');
echo CHtml::dropDownList('cat', $plugins->categories, array(''=>'All Categories') + array_combine($cats, $cats));
echo CHtml::endForm();
echo CHtml::script('$("#cat").change(function() {
            $(this).closest("form").submit();
        });
');
?>

<?php if(Yii::app()->user->hasFlash('server')): ?>
<div class="flash-error">
    <?php echo Yii::app()->user->getFlash('server'); ?>
</div>
<?php endif ?>

<?php
$cols = array(
    array('name'=>'plugin_name', 'type'=>'raw', 'value'=>'CHtml::link(CHtml::encode($data->plugin_name), array("bgPlugin", "id"=>'.$model->id.', "installed"=>'.($installed ? 'true' : 'false').', "name"=>$data->name))'),
    array('name'=>'desc', 'type'=>'html', 'value'=>'substr($data->desc, 0, 64).(strlen($data->desc) > 64 ? "..." : "")'),
    array('name'=>'status'),
);

echo CHtml::css('.topalign td { vertical-align: top }' );
$this->widget('zii.widgets.grid.CGridView', array(
    'id'=>'configs-grid',
    'filter'=>$plugins,
    'ajaxUpdate'=>false,
    'rowCssClass'=>array('even topalign', 'odd topalign'),
    'dataProvider'=>$plugins->search($installed ? $model->id : 0),
    'columns'=>$cols,
)); ?>



