<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

/**
 * Displays a role selection dropdown and calls an ajax function on change
 */
class FtpAccessDropdownColumn extends CGridColumn
{
    protected function renderDataCellContent($row, $data)
    {
        if (Yii::app()->params['view_role'] == 'admin')
            return;
        $access = $data->getServerFtpAccess(Yii::app()->params['view_server_id']);
        $options = array(
            ''      => Yii::t('mc', 'No Access'),
            'ro'    => Yii::t('mc', 'Read only'),
            'rw'    => Yii::t('mc', 'Full access')
        );
        echo CHtml::dropDownList('ftpAccess_'.$data->id, $access, $options,
                array('ajax'=>array('type'=>'POST', 'data'=>array('ajax'=>'ftpAccess', 'user'=>$data->id,
                        Yii::app()->request->csrfTokenName=>Yii::app()->request->csrfToken,
                        'ftpAccess'=>"js:$('#ftpAccess_".$data->id."').val()"),
                        'success'=>'js:function(e) {if(e)alert(e);}')));
        echo '&nbsp;&nbsp;&nbsp;'.CHtml::encode($data->name).'.'.Yii::app()->params['view_server_id'];
    }
}
