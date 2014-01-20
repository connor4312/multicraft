<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle=Yii::app()->name . ' - '.Yii::t('mc', 'Minecraft FTP Client');
$this->breadcrumbs=array(
    Yii::t('mc', 'FTP Client')=>array('index', 'id'=>$server->id),
    $server->name=>array('browse', 'id'=>$server->id),
    Yii::t('mc', 'FTP'),
);

Yii::app()->getClientScript()->registerCoreScript('jquery');
?>

<?php echo $css; ?>
<?php echo $js ?>

<?php echo CHtml::script('$(document).ready(function() { '.$onload.' });') ?>


<?php echo $body ?>
