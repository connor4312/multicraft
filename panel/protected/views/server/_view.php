<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
?>
<a class="row server-list" href="<?php echo CHTML::normalizeUrl(array('view', 'id'=>$data->id)) ?>">
    <?php
    $pl = 0;
    $img = $data->suspended ? '<i class="fa fa-times"></i>' : '<i class="fa fa-refresh fa-spin"></i>';
    if (!Yii::app()->params['ajax_serverlist'])
    {
        $pl = $data->getOnlinePlayers();
        $img = $pl >= 0 ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>';
    }
    ?>
    <span class="col-xs-1">
        <?php echo $img ?>
    </span>
    <span class="col-xs-6">
        <?php echo CHtml::encode($data->name) ?>
    </span>
    <span class="col-xs-5">
        <span id="sv_status_<?php echo $data->id ?>">
        <?php
        if ($data->suspended)
            echo Yii::t('mc', 'Suspended').'</span>';
        else
        {
            if (!Yii::app()->params['ajax_serverlist'])
            {
                if ($pl >= 0)
                    echo CHtml::encode($pl).' /  '.CHtml::encode($data->players).' '.Yii::t('mc', 'Players');
                else
                    echo Yii::t('mc', 'Offline').($pl == -2 ? ' ('.Yii::t('mc', 'error').')' : '');
            }
            else
                echo Yii::t('mc', 'Pending');
            echo '</span>';
            echo '<span id="sv_maxplr_'.$data->id.'" style="display: none"> /  '.CHtml::encode($data->players).' '.Yii::t('mc', 'Players').'</span>';
            if ($pl >= 0 && Yii::app()->user->can($data->id, 'chat'))
            {
                echo '<span id="sv_chatlink_'.$data->id.'"'.(Yii::app()->params['ajax_serverlist'] ? ' style="display: none"' : '')
                    .'> ('.CHtml::link(Yii::t('mc', 'Chat'), array('chat', 'id'=>$data->id)).')</span>';
            }
        }
        ?>
    </span>
    <?php
        if (!!Yii::app()->params['ajax_serverlist'] && !$data->suspended)
            echo CHtml::script('get_status('.$data->id.');')
    ?>
</a>
