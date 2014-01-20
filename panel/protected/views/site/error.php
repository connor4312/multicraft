<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle=Yii::app()->name . ' - '.Yii::t('mc', 'Error');
$this->breadcrumbs=array(
    Yii::t('mc', 'Error'),
);
?>

<h2><?php echo Yii::t('mc', 'Error') ?> <?php echo $code; ?></h2>

<div class="error">
<?
if ($type == 'RawHttpException')
    echo $message;
else
    echo CHtml::encode($message);
?>
</div>
