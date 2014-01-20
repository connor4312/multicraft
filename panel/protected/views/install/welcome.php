<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle=Yii::app()->name . ' - '.Yii::t('mc', 'Multicraft Installer');

$this->breadcrumbs = array('Welcome');

$this->layout = '//layouts/column1';
?>
<br/>
<br/>
<br/>
<div class="centered">
<h2>Welcome to Multicraft!</h2>
This installer will guide you through the Multicraft Server Manager setup.<br/>
<br/>
To disable the installer please remove the file "installer.php".<br/>
<br/>
<?php echo CHtml::beginForm(array('index', 'step'=>'requirements'), 'get') ?>
<?php echo CHtml::submitButton('Start Installation') ?>
<?php echo CHtml::endForm() ?>
</div>
