<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class ScheduleController extends Controller
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
                'actions'=>array('index','view','create','delete'),
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

    public function handlePost($model)
    {
        $model->attributes=$_POST['Schedule'];
        $model->scheduled_ts = @strtotime(@$_POST['scheduled_ts']);
        if (@$_POST['ival_do'])
        {
            $mult = 1;
            switch(@$_POST['ival_type'])
            {
            case 2:
                $mult *= 24;
            case 1:
                $mult *= 60;
            default:
                $mult *= 60;
            }
            $model->interval = (@$_POST['ival_nr'] + 1) * $mult;
        }
        else
            $model->interval = 0;
        return $model;
    }

    public function checkActionAccess($model)
    {
        if (!Yii::app()->user->isSuperuser()
            && ($model->hidden || !Yii::app()->user->can($model->server_id, 'manage commands', true)))
            Yii::app()->user->deny();
        $cfg = ServerConfig::model()->findByPk($model->server_id);
        if (!Yii::app()->user->isSuperuser()
            && (!$cfg || !$cfg->user_schedule))
            Yii::app()->user->deny();
    }

    public function actionView($id)
    {
        $model = $this->loadModel($id);
        $this->checkActionAccess($model);

        if(isset($_POST['Schedule']))
        {
            if (Yii::app()->user->isSuperuser())
                $model->scenario = 'superuser';
            $model = $this->handlePost($model);
            if($model->save())
            {
                Yii::log('Updated task '.$model->id);
                $this->redirect(array('view','id'=>$model->id));
            }
        }

        $ival_nr = $ival_type = 0;
        if ($model->interval)
        {
            if (!($model->interval % (24 * 3600)))
            {
                $ival_type = 2;
                $ival_nr = $model->interval / (24 * 3600) - 1;
            }
            else if (!($model->interval % 3600))
            {
                $ival_type = 1;
                $ival_nr = $model->interval / 3600 - 1;
            }
            else
            {
                $ival_type = 0;
                $ival_nr = $model->interval / 60 - 1;
            }
        }

        $this->render('view',array(
            'model'=>$model,
            'sv'=>$model->server_id,
            'ival_nr'=>$ival_nr,
            'ival_type'=>$ival_type,
        ));
    }

    public function actionCreate($sv = false)
    {
        $model=new Schedule;
        $model->server_id = $sv;
        $this->checkActionAccess($model);

        if(isset($_POST['Schedule']))
        {
            if (Yii::app()->user->isSuperuser())
                $model->scenario = 'superuser';
            $model = $this->handlePost($model);
            if($model->save())
            {
                Yii::log('Created task '.$model->id);
                $this->redirect(array('view','id'=>$model->id));
            }
        }

        $this->render('view',array(
            'model'=>$model,
            'sv'=>$sv,
            'ival_nr'=>0,
            'ival_type'=>0,
        ));
    }

    public function actionDelete($id)
    {
        if(Yii::app()->request->isPostRequest)
        {
            $model=$this->loadModel($id);
            $this->checkActionAccess($model);
            $sv = $model->server_id;
            Yii::log('Deleting task '.$model->id);
            $model->delete();

            if(!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index', 'sv'=>$sv));
        }
        else
            throw new CHttpException(400,Yii::t('mc', 'Invalid request. Please do not repeat this request again.'));
    }

    public function actionIndex($sv)
    {
        $model=new Schedule('search');
        $model->unsetAttributes();
        $model->server_id = $sv;
        if (!Yii::app()->user->isSuperuser())
            $model->hidden = 0;
        $this->checkActionAccess($model);
        if(isset($_GET['Schedule']))
            $model->attributes=$_GET['Schedule'];

        $this->render('index',array(
            'model'=>$model,
            'sv'=>$sv,
        ));
    }

    public function actionAdmin()
    {
        $model=new Schedule('search');
        $model->unsetAttributes();
        if(isset($_GET['Schedule']))
            $model->attributes=$_GET['Schedule'];

        $this->render('admin',array(
            'model'=>$model,
        ));
    }

    public function loadModel($id)
    {
        $model=Schedule::model()->findByPk((int)$id);
        if($model===null)
            throw new CHttpException(404,Yii::t('mc', 'The requested page does not exist.'));
        return $model;
    }

    protected function performAjaxValidation($model)
    {
        if(isset($_POST['ajax']) && $_POST['ajax']==='schedule-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
