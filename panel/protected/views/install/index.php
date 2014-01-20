<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle=Yii::app()->name . ' - '.Yii::t('mc', 'Multicraft Installer');
$this->breadcrumbs = array(
    Yii::t('mc', 'Installer')=>array('index'),
    InstallController::$stepLabels[$idx],
);

$this->menu = array(
    array('label'=>Yii::t('mc', 'Back'), 'url'=>array('index', 'step'=>$prevStep), 'icon'=>'back', 'visible'=>$prevStep && $prevStep != $step),
);

Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/css/detailview.css');
Yii::app()->getClientScript()->registerCoreScript('jquery');

echo CHtml::css('
    .semi
    {
       opacity: 0.5;
       filter: alpha(opacity=50);
       zoom: 1;
    }
    .stepbox
    {
        float:left;
        width: 90px;
        height: 90px;
        background-image: url(\''.Theme::themeFile('images/installer/bg.png').'\');
    }
    .nextbox
    {
        float:left;
        padding-top: 32px;
        height: 30px;
        width: 24px;
    }
    img.step
    {
        margin-top: 13px;
        margin-left: 13px;
    }
    .centered
    {
        text-align: center;
    }
');

$steps = InstallController::$steps;
$labels = array_combine($steps, InstallController::$stepLabels);
$steps = array_splice($steps, 1, -1);
if ($step != end(InstallController::$steps))
{
?>
<div style="height: 90px; padding-left: <?php echo $step == 'welcome' ? '135px; padding-top: 29'  : 40 ?>px">
<?php
    foreach ($steps as $s)
    {
    ?>
    <div class="stepbox <?php echo ($s == $step || $step == 'welcome' ? '' : ' semi') ?>">
        <?php
            $img = Theme::img('installer/'.$s.'.png', '', array('class'=>'step'));
            if ($step == 'welcome')
                echo $img.'<br/><br/><center style="font-size: 11px">'.preg_replace('/&nbsp;/', '', $labels[$s]).'</center>';
            else
                echo CHtml::link($img, array('index', 'step'=>$s));
        ?>
    </div>
    <?php if ($s != end($steps)): ?>
    <div class="nextbox">
        <?php echo CHtml::link(Theme::img('installer/next.png'), array('index', 'step'=>$s)) ?>
    </div>
    <?php endif ?>
    <?php
    }
?>
<div style="clear: both"></div>
</div>
<br/>
<?php
}
?>
<?php
if (count($p['actions'])): ?>
<b><?php echo InstallController::$stepLabels[$idx] ?></b><br/>
<br/>
<div class="flash-<?php echo $p['success'] ? 'success' : 'notice' ?>">
<ul style="margin-bottom: 0">
    <?php foreach ($p['actions'] as $a): ?>
    <li><?php echo $a ?></li>
    <?php endforeach ?>
</ul>
</div>
<?php endif;

$this->renderPartial($step, array('p'=>$p));
?>

<?php echo CHtml::beginForm() ?>
<?php echo CHtml::endForm() ?>

