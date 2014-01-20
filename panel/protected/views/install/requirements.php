<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle=Yii::app()->name . ' - '.Yii::t('mc', 'Multicraft Installer');

echo CHtml::css('
table.result
{
    background:#f0f1f4 none repeat scroll 0% 0%;
    border-collapse:collapse;
    width:100%;
}

table.result th
{
    background:#888;
    color: #fff;
    text-shadow: 0px 1px 0px #555555;
    text-align:left;
    font-weight: normal;
}

table.result th, table.result td
{
    border-bottom: 1px solid #dedede;
    padding: 6px;
}

td.passed
{
    background-color: #60BF60;
    border: 1px solid silver;
    padding: 2px;
    color: #fff;
    text-shadow: 0px 1px 0px #555;
}

td.warning
{
    background-color: #FFFFBF;
    border: 1px solid silver;
    padding: 2px;
    color: #555;
    text-shadow: 0px 0px 0px;
}

td.failed
{
    background-color: #FF8080;
    border: 1px solid silver;
    padding: 2px;
    color: #fff;
    text-shadow: 0px 1px 0px #555;
}
');
?>
<?php if($p['result']>0): ?>
Your server configuration satisfies all requirements.
<?php elseif($p['result']<0): ?>
Your server configuration satisfies the minimum requirements. Please pay attention to the warnings listed below if you intend to use the corresponding functionality.
<?php else: ?>
Unfortunately your server configuration does not satisfy the requirements.
<?php endif; ?>
<br/><br/>
<?php if ($p['result'] != 0): ?>
    <?php echo CHtml::beginForm(array('index', 'step'=>'config')) ?>
    <?php echo CHtml::submitButton('Continue') ?>
    <?php echo CHtml::endForm() ?>
<?php else: ?>
    <?php echo CHtml::beginForm(array('index', 'step'=>'config')) ?>
    <?php echo CHtml::submitButton('Continue Anyway', array('confirm'=>'You may encounter errors during the installer and issues with missing functionality or security features are possible.')) ?>
    <?php echo CHtml::endForm() ?>
<?php endif ?>
</p>

<b>Details</b><br/>
<br/>

<table class="result">
<tr><th>Name</th><th>Result</th><th>Required By</th><th>Memo</th></tr>
<?php foreach($p['requirements'] as $requirement): ?>
<tr>
    <td>
    <?php echo $requirement[0]; ?>
    </td>
    <td class="<?php echo $requirement[2] ? 'passed' : ($requirement[1] ? 'failed' : 'warning'); ?>">
    <?php echo $requirement[2] ? 'Passed' : ($requirement[1] ? 'Failed' : 'Warning'); ?>
    </td>
    <td>
    <?php echo $requirement[3]; ?>
    </td>
    <td>
    <?php echo $requirement[4]; ?>
    </td>
</tr>
<?php endforeach; ?>
</table>

<br/>
<br/>

