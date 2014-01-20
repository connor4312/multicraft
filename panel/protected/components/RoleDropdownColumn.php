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
class RoleDropdownColumn extends CGridColumn
{
    protected function renderDataCellContent($row, $data)
    {
        $role = $data->getServerRole(Yii::app()->params['view_server_id']);
        $options[''] = Yii::t('mc', 'Unassigned');
        for($i = 0; $i < count(User::$roles); $i++)
            $options[User::$roles[$i]] = User::getRoleLabels($i);
        array_pop($options);
        if ($role == 'owner')
        {
            echo Yii::t('mc', 'Owner');
            return;
        }
        if (Yii::app()->params['view_role'] == 'admin')
        {
            if ($role == 'admin')
            {
                echo $options['admin'];
                return;
            }
            array_pop($options);
        }
        echo CHtml::dropDownList('role_'.$data->id, $role, $options,
                array('ajax'=>array('type'=>'POST', 'data'=>array('ajax'=>'role', 'user'=>$data->id,
                    Yii::app()->request->csrfTokenName=>Yii::app()->request->csrfToken,
                        'role'=>"js:$('#role_".$data->id."').val()"), 'success'=>'js:function(e) {if(e)alert(e);}')));
    }
}
