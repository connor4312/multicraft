<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

$status = $this->getDaemonStatus($data->id);
?>

<div>
<div style="float: left"><b><?php echo ($data->name ? CHtml::encode($data->name) : Yii::t('admin', 'Daemon')) ?> ID <?php echo $data->id ?></b> (<?php echo CHtml::link(Yii::t('admin', 'Remove connection'), array('removeDaemon', 'id'=>$data->id),
        array('confirm'=>Yii::t('admin', 'This will only remove the daemon entry for this server from the database. If the daemon is still running or is started again this entry will be put back in.'))) ?>)
</div>
</div>
<div style="clear: both"></div>
<div class="<?php echo $status['class'] ?>" style="padding: 0px; margin: 0px">
    <table class="stdtable">
    <tr>
        <td style="padding: 4px" width="20%"><?php echo Yii::t('admin', 'Address') ?></td>
        <td style="padding: 4px" width="10%"><?php echo Yii::t('admin', 'Servers') ?></td>
        <td style="padding: 4px" width="20%"><?php echo Yii::t('admin', 'Memory') ?></td>
        <td style="padding: 4px" width="10%"><?php echo Yii::t('admin', 'Version') ?></td>
        <td style="padding: 4px" width="10%"><?php echo Yii::t('admin', 'Latest') ?></td>
        <td style="padding: 4px" width="30%"><?php echo Yii::t('admin', 'Last Check') ?></td>
    </tr>
    <tr class="titlerow">
        <td>
            <?php echo CHtml::encode($data->ip.':'.$data->port) ?>
        </td>
        <td>
            <?php
                $sql = 'select count(*) from `server` where `daemon_id`=?';
                $cmd = Yii::app()->bridgeDb->createCommand($sql);
                $cmd->bindValue(1, (int)$data->id);
                echo $cmd->queryScalar();
            ?>
        </td>
        <td>
            <?php
            echo $data->usedMemory;
            if (isset($data->memory) && $data->memory)
                echo ' / '.$data->memory
            ?> MB
        </td>
        <td>
            <?php echo $status['version'] ?>
        </td>
        <td>
            <?php echo $status['remote'] ?>
        </td>
        <td>
            <?php echo $status['time'] ?>
        </td>
    </tr>
    <tr>
        <td colspan="6">
            <?php echo @$status['info'] ?>
        </td>
    </tr>
    </table>
</div>
