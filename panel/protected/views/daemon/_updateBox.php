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
<tr class="row">
    <td class="col-md-3">
        <h4><?php echo ($data->name ? CHtml::encode($data->name) : Yii::t('admin', 'Daemon')) ?> <small>ID <?php echo $data->id ?></small></h4>
        <div class="daemon-time"> <?php echo $status['time'] ?></div>
    </td>
    <td class="col-md-5">
        <div class="<?php echo $status['class'] ?>"><?php echo $status['content'] ?></div>
    </td>
    <td class="col-md-2">
    <?php
        echo CHtml::button(Yii::t('admin', 'Download'), array('onclick'=>'javascript:download('.$data->id.')', 'class' => 'btn btn-default btn-block'));
    ?>
    </td>
    <td class="col-md-2">
    <?php
        echo CHtml::button(Yii::t('admin', 'Install'), array('onclick'=>'javascript:install('.$data->id.')', 'class' => 'btn btn-primary btn-block'));
    ?>
    </td>
</tr>

    
