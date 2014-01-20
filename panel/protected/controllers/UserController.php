<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class UserController extends Controller
{
    public $layout='//layouts/column2';

    public function filters()
    {
        return array(
            'accessControl',
        );
    }

    public function accessRules()
    {
        return array(
            array('allow',
                'actions'=>array('index', 'view'),
                'users'=>array('@'),
            ),
            array('allow',
                'actions'=>array('create','update','delete'),
                'expression'=>'$user->isSuperuser()',
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    public function actionView($id)
    {
        if (!Yii::app()->user->isSuperuser() && (Yii::app()->params['hide_userlist'] === true)
            && (Yii::app()->user->id != $id))
        {
            throw new CHttpException(403, Yii::t('mc', 'You are not authorized to perform this action.'));
        }
        $model = $this->loadModel($id);
        $model->scenario = Yii::app()->user->isSuperuser() ? 'superuserUpdate' : 'update';

        $edit = Yii::app()->user->isSuperuser() || Yii::app()->user->name == $model->name;

        if(isset($_POST['User']) && $edit)
        {
            if (Yii::app()->params['demo_mode'] == 'enabled' && in_array($model->name, array('admin', 'owner', 'user')))
            {
                Yii::app()->user->setFlash('user', Yii::t('mc', 'Function disabled in demo mode.'));
                $this->redirect(array('view','id'=>$model->id));
            }
            if (Yii::app()->user->name !== Yii::app()->user->superuser
                    && $model->name == Yii::app()->user->superuser)
            {
                Yii::app()->user->setFlash('user', Yii::t('mc', 'Access Denied'));
                $this->redirect(array('view','id'=>$model->id));
            }
            $nameBackup = $model->name;
            $pwBackup = $model->password;
            $model->attributes=$_POST['User'];
            if (!strlen($_POST['User']['password']))
                $model->password = $pwBackup;
            $model->sendData = @$_POST['send_data'];
            if (!Yii::app()->user->isSuperuser() || $model->name == Yii::app()->user->superuser
                || $nameBackup == Yii::app()->user->superuser)
                $model->name = $nameBackup;
            if($model->save())
            {
                Yii::log('Updated user '.$model->id);
                Yii::app()->user->setFlash('user', Yii::t('mc', 'User saved.'));
                $this->redirect(array('view','id'=>$model->id));
            }
        }
        else if (isset($_POST['action']) && Yii::app()->user->isSuperuser())
        {
            switch ($_POST['action'])
            {
            case 'new_api_key':
                $model->api_key = substr(md5((string)microtime(true)), 0, 20);
                $model->save(false);
                Yii::log('Generated API key for user '.$model->id);
                Yii::app()->user->setFlash('user', Yii::t('mc', 'New API key generated.'));
                $this->redirect(array('view','id'=>$model->id));
                break;
            case 'del_api_key':
                $model->api_key = '';
                $model->save(false);
                Yii::log('Deleted API key for user '.$model->id);
                Yii::app()->user->setFlash('user', Yii::t('mc', 'API key deleted.'));
                $this->redirect(array('view','id'=>$model->id));
                break;
            }
        }
        $model->password = '';

        $allRoles = array_combine(User::$roles, User::getRoleLabels());
        $allRoles['superuser'] = Yii::t('mc', 'Superuser');

        $servers = array();
        $spp = 10;
        if (Yii::app()->user->isSuperuser())
        {
            if ($spp = Setting::model()->findByPk('serversPerPage'))
                $spp = max(1, (int)$spp->value);
            $sql = 'select `server_id` from `user_server` where `user_id`=? and `role`=\'owner\'';
            $cmd = Yii::app()->db->createCommand($sql);
            $cmd->bindValue(1, $model->id);
            $ids = $cmd->queryColumn();
            $servers = Server::model()->findAllByAttributes(array('id'=>array_values($ids)));
        }
        $this->render('view',array(
            'model'=>$model,
            'allRoles'=>$allRoles,
            'edit'=>$edit,
            'servers'=>new CArrayDataProvider($servers, array(
                'sort'=>array(
                    'attributes'=>array(
                        'name', 
                    ),
                ),
                'pagination'=>array('pageSize'=>$spp),
            )),
        ));
    }

    public function actionCreate()
    {
        $model=new User('create');

        if(isset($_POST['User']))
        {
            $model->attributes=$_POST['User'];
            if (!strlen($model->password) &&  Yii::app()->params['mail_welcome'])
                $model->password = substr(md5(rand().$model->name), 5, 13);
            $model->sendData = @$_POST['send_data'];
            if($model->save())
            {
                Yii::log('Created user '.$model->id);
                $this->redirect(array('view','id'=>$model->id));
            }
        }

        $allRoles = array_combine(User::$roles, User::getRoleLabels());
        $allRoles['superuser'] = Yii::t('mc', 'Superuser');

        $this->render('view',array(
            'model'=>$model,
            'allRoles'=>$allRoles,
            'edit'=>true,
        ));
    }

    public function actionDelete($id)
    {
        $model = $this->loadModel($id);
        if (Yii::app()->params['demo_mode'] == 'enabled' && in_array($model->name, array('admin', 'owner', 'user')))
        {
            Yii::app()->user->setFlash('user', Yii::t('mc', 'Function disabled in demo mode.'));
            $this->redirect(array('view','id'=>$model->id));
        }
        if(Yii::app()->request->isPostRequest)
        {
            if ($model->name != Yii::app()->user->superuser)
            {
                Yii::log('Deleting user '.$model->id);
                $model->delete();   
            }

            if(!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
        }
        else
            throw new CHttpException(400,Yii::t('mc', 'Invalid request. Please do not repeat this request again.'));
    }

    public function actionIndex()
    {
        if (!Yii::app()->user->isSuperuser() && Yii::app()->params['hide_userlist'])
            throw new CHttpException(403, Yii::t('mc', 'You are not authorized to perform this action.'));
        $model=new User(Yii::app()->user->isSuperuser() ? 'search' : 'userSearch');
        $model->unsetAttributes();
        if(isset($_GET['User']))
            $model->attributes=$_GET['User'];

        $this->render('index',array(
            'model'=>$model,
        ));
    }

    public function loadModel($id)
    {
        $model=User::model()->findByPk((int)$id);
        if($model===null)
            throw new CHttpException(404,Yii::t('mc', 'The requested page does not exist.'));
        return $model;
    }

    protected function performAjaxValidation($model)
    {
        if(isset($_POST['ajax']) && $_POST['ajax']==='user-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
