<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class CommandController extends Controller
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
                'actions'=>array('index','view'),
                'users'=>array('*'),
            ),
            array('allow',
                'actions'=>array('create','update','delete'),
                'users'=>array('@'),
            ),
            array('allow',
                'actions'=>array('admin'),
                'expression'=>'$user->isSuperuser()',
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    public function actionView($id)
    {
        $model = $this->loadModel($id);
        Yii::app()->user->can($model->server_id, 'view command', true);
        if ($model->hasAttribute('hidden') && !Yii::app()->user->isSuperuser() && $model->hidden)
            Yii::app()->user->deny();

        if(isset($_POST['Command']) && Yii::app()->user->can($model->server_id, 'manage commands'))
        {
            if (Yii::app()->user->isSuperuser())
                $model->scenario = 'superuser';
            $model->attributes=$_POST['Command'];
            if($model->save())
            {
                Yii::log('Updated command '.$model->id);
                Yii::app()->user->setFlash('command', Yii::t('mc', 'Command saved.'));
                $this->redirect(array('view','id'=>$model->id));
            }
        }
        $this->render('view',array(
            'model'=>$model,
            'edit'=>Yii::app()->user->can($model->server_id, 'manage commands'),
            'sv'=>$model->server_id,
        ));
    }

    public function actionCreate($sv = false)
    {
        if (!Yii::app()->user->isSuperuser()
            && !Yii::app()->user->can($sv, 'manage commands', true))
            Yii::app()->user->deny();

        $model=new Command;
        $model->server_id = $sv;

        if(isset($_POST['Command']))
        {
            if (Yii::app()->user->isSuperuser())
                $model->scenario = 'superuser';
            $model->attributes=$_POST['Command'];
            if($model->save())
            {
                Yii::log('Created command '.$model->id);
                $this->redirect(array('view','id'=>$model->id));
            }
        }

        $this->render('view',array(
            'model'=>$model,
            'sv'=>$sv,
            'edit'=>true,
        ));
    }

    public function actionDelete($id)
    {
        if(Yii::app()->request->isPostRequest)
        {
            $model = $this->loadModel($id);
            $sv = $model->server_id;
            Yii::app()->user->can($model->server_id, 'manage commands', true);
            if ($model->hasAttribute('hidden') && !Yii::app()->user->isSuperuser() && $model->hidden)
                Yii::app()->user->deny();
            Yii::log('Deleting command '.$model->id);
            $model->delete();

            if(!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index', 'sv'=>$sv));
        }
        else
            throw new CHttpException(400,Yii::t('mc', 'Invalid request. Please do not repeat this request again.'));
    }

    public function actionIndex($sv)
    {
        Yii::app()->user->can($sv, 'manage players', true);
        $model=new Command(Yii::app()->user->isSuperuser() ? 'search' : 'serverSearch');
        $model->unsetAttributes();
        $model->server_id = $sv;
        if ($model->hasAttribute('hidden') && !Yii::app()->user->isSuperuser())
            $model->hidden = 0;
        if(isset($_GET['Command']))
            $model->attributes=$_GET['Command'];

        $this->render('index',array(
            'model'=>$model,
            'sv'=>$sv,
        ));
    }

    public function actionAdmin()
    {
        $model=new Command('search');
        $model->unsetAttributes();
        if(isset($_GET['Command']))
            $model->attributes=$_GET['Command'];

        $this->render('admin',array(
            'model'=>$model,
        ));
    }

    public function loadModel($id)
    {
        $model=Command::model()->findByPk((int)$id);
        if($model===null)
            throw new CHttpException(404,Yii::t('mc', 'The requested page does not exist.'));
        return $model;
    }

    protected function performAjaxValidation($model)
    {
        if(isset($_POST['ajax']) && $_POST['ajax']==='command-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
