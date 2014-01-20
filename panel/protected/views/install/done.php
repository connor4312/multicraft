<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle=Yii::app()->name . ' - '.Yii::t('mc', 'Multicraft Installer');

?>
<h2>Congratulations!</h2>
Your Multicraft installation has been completed!<br/>
<br/>
For security reasons please <b>delete the install.php</b> file.<br/>
<br/>
<?php echo CHtml::beginForm(Yii::app()->request->getBaseUrl(true)) ?>
<?php echo CHtml::submitButton('Continue to Multicraft') ?>
<?php echo CHtml::endForm() ?>
<br/>
