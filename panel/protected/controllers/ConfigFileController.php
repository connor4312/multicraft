<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class ConfigFileController extends Controller
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
        if(isset($_POST['ConfigFile']))
        {
            $model->attributes=$_POST['ConfigFile'];
            if($model->save())
            {
                Yii::log('Updated config file entry '.$model->id);
                Yii::app()->user->setFlash('configFile', Yii::t('mc', 'Config File settings saved.'));
                $this->redirect(array('view','id'=>$model->id));
            }
        }
        $this->render('view',array(
            'model'=>$model,
        ));
    }

    public function actionCreate()
    {
        $model=new ConfigFile;

        if(isset($_POST['ConfigFile']))
        {
            $model->attributes=$_POST['ConfigFile'];
            if($model->save())
            {
                Yii::log('Created config file entry '.$model->id);
                $this->redirect(array('view','id'=>$model->id));
            }
        }

        $this->render('view',array(
            'model'=>$model,
        ));
    }

    public function actionDelete($id)
    {
        if(Yii::app()->request->isPostRequest)
        {
            $model = $this->loadModel($id);
            Yii::log('Deleting config file entry '.$model->id);
            $model->delete();

            if(!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
        }
        else
            throw new CHttpException(400,Yii::t('mc', 'Invalid request. Please do not repeat this request again.'));
    }

    public function actionIndex()
    {
        $model=new ConfigFile('search');
        $model->unsetAttributes();  // clear any default values
        if(isset($_GET['ConfigFile']))
            $model->attributes=$_GET['ConfigFile'];

        $this->render('index',array(
            'model'=>$model,
        ));
    }

    public function loadModel($id)
    {
        $model=ConfigFile::model()->findByPk((int)$id);
        if($model===null)
            throw new CHttpException(404,Yii::t('mc', 'The requested page does not exist.'));
        return $model;
    }

    protected function performAjaxValidation($model)
    {
        if(isset($_POST['ajax']) && $_POST['ajax']==='config-file-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
