<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
?>
<div class="view">
    <?php
    $pl = $data->getOnlinePlayers();
    $img = $pl >= 0 ? 'online.png' : 'offline.png';
    ?>
    <table style="height: 100%; table-layout: fixed">
    <colgroup>
        <col style="width: 35px"/>
        <col/>
        <col/>
    </colgroup>
    <tr>
        <td style="padding-left: 10px" id="sv_icon_<?php echo $data->id ?>">
            <?php echo Theme::img($img) ?>
        </td>
        <td style="width: 50%; max-width: 330px; overflow: hidden; vertical-align: middle">
            <?php echo CHtml::link(CHtml::encode($data->name), array('server/view', 'id'=>$data->id), array('style'=>'display: block; max-height: 28px')); ?></a>
        </td>
        <td>
            <span id="sv_status_<?php echo $data->id ?>">
            <?php
            if ($data->suspended)
                echo Yii::t('mc', 'Suspended').'</span>';
            else
            {
                if ($pl >= 0)
                    echo CHtml::encode($pl).' /  '.CHtml::encode($data->players).' '.Yii::t('mc', 'Players');
                else
                    echo Yii::t('mc', 'Offline').($pl == -2 ? ' ('.Yii::t('mc', 'error').')' : '');
                echo '</span>';
                echo '<span id="sv_maxplr_'.$data->id.'" style="display: none"> /  '.CHtml::encode($data->players).' '.Yii::t('mc', 'Players').'</span>';
            }
            ?>
        </td>
    </tr>
    </table>
</div>
