<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

$status = $this->getJarStatus($data->id);
?>
<?php echo ($data->name ? CHtml::encode($data->name) : Yii::t('admin', 'Daemon')) ?> ID <?php echo $data->id ?>
<div class="<?php echo $status['class'] ?>">
    <div style="float: left; width: 12%">
    <?php echo $status['time'] ?>
    </div>
    <div style="float: left; width: 67%">
    <?php echo $status['content'] ?>
    </div>
    <div style="float: right">
    <?php
        echo CHtml::button(Yii::t('admin', 'Download'), array('onclick'=>'javascript:download('.$data->id.')'));
        echo CHtml::button(Yii::t('admin', 'Install'), array('onclick'=>'javascript:install('.$data->id.')'));
    ?>
    </div>
    <div style="clear: both"></div>
</div>

    
