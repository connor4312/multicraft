<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle=Yii::app()->name . ' - '.Yii::t('mc', 'About');
$this->breadcrumbs=array(
    Yii::t('mc', 'About'),
);

$this->menu=array(
    array(
        'label'=>Yii::t('mc', 'Back'),
        'url'=>array('', 'view'=>'home'),
        'icon'=>'back',
    ),
);

echo CHtml::css('
.stdtable td
{
    padding: 20px;
    background-color: white;
    text-align: center;
    border-top: 1px solid #dfdfdf;
}
.stdtable td.left
{
    border-right: 1px solid #dfdfdf;
}
');
?>
<div style="margin-right: -8px; float: right"><?php echo Theme::img('about/swissmade.png') ?></div>
<br/>
<h2><?php echo Yii::t('mc', 'About Multicraft') ?></h2>
<?php echo 'Copyright &copy; 2010-2012 by '.CHtml::link('xhost.ch GmbH', 'http://www.xhost.ch/').'. All Rights Reserved.'; ?><br/>
<br/>
<br/>
<?php echo Yii::t('mc', 'Multicraft uses the following technologies:') ?><br/>
<br/>
<table class="stdtable">
<tr>
    <td class="left" style="width: 50%">
        <?php echo CHtml::link(Theme::img('about/python.png'), 'http://www.python.org'); ?>
    </td>
    <td style="width: 50%">
        <?php echo CHtml::link(Theme::img('about/pyftpdlib.png'), 'http://code.google.com/p/pyftpdlib/') ?>
    </td>
</tr>
<tr>
    <td class="left">
        <?php echo CHtml::link(Theme::img('about/psutil.png'), 'http://code.google.com/p/psutil/') ?>
    </td>
    <td>
        <?php echo CHtml::link(Theme::img('about/php.png'), 'http://www.php.net'); ?>
    </td>
</tr>
<tr>
    <td class="left">
        <?php echo CHtml::link(Theme::img('about/yii.png'), 'http://www.yiiframework.com'); ?>
    </td>
    <td>
        <?php echo CHtml::link(Theme::img('about/sqlite.png'), 'http://www.sqlite.org'); ?>
    </td>
</tr>
<tr>
    <td class="left">
        <?php echo CHtml::link(Theme::img('about/net2ftp.png'), 'http://www.net2ftp.com/'); ?>
    </td>
    <td>
        <?php echo CHtml::link(Theme::img('about/oil.png'), 'http://openiconlibrary.sourceforge.net'); ?>
    </td>
</tr>
<tr>
    <td colspan="2">
        <?php echo CHtml::link(Theme::img('about/xhost.png'), 'http://www.xhost.ch/'); ?>
    </td>
</tr>
<tr>
    <td colspan="2"> 
        <?php echo CHtml::link(Theme::img('about/multicraft.png'), 'http://www.multicraft.org/'); ?>
    </td>
</tr>
</table>
<br/>
<br/>
