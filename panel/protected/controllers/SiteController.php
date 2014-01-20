<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class SiteController extends Controller
{
    public function actions()
    {
        return array(
            'captcha'=>array(
                'class'=>'CCaptchaAction',
                'backColor'=>0xFFFFFF,
            ),
            'page'=>array(
                'class'=>'CViewAction',
            ),
        );
    }

    public function actionIndex()
    {
        $this->redirect(array('site/page', 'view'=>'home'));
    }

    public function actionError()
    {
        if($error=Yii::app()->errorHandler->error)
        {
            if(Yii::app()->request->isAjaxRequest)
                echo $error['message'];
            else
                $this->render('error', $error);
        }
    }

    public function actionReport()
    {
        $model=new ReportForm;
        if(isset($_POST['ReportForm']))
        {
            $model->attributes=$_POST['ReportForm'];
            if($model->validate())
            {
                if (!$model->email)
                    $model->email = 'nobody@localhost';
                if (!$model->name)
                    $model->name = 'Anonymous';
                $model->report = 'Sent through: '.Yii::app()->request->getBaseUrl(true)."\n\n".$model->report;
                $message = new YiiMailMessage;
                $message->setTo(Yii::app()->params['admin_email']);
                $message->setFrom(array($model->email=>$model->name));
                $message->setSubject(Yii::t('mc', 'Support Form'));
                $message->setBody($model->report);
                Yii::app()->mail->send($message);
                Yii::app()->user->setFlash('report',Yii::t('mc', 'Thank you for contacting us!'));
                Yii::log('Support form submitted');
                $this->refresh();
            }
        }
        $this->render('report',array('model'=>$model));
    }

    public function actionLogin($name = '', $password = '')
    {
        $model=new LoginForm;

        if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        if(isset($_POST['LoginForm']))
        {
            $model->attributes=$_POST['LoginForm'];
            $logTries = Yii::app()->params['login_tries'];
            if ($logTries)
            {
                $now = time();
                $session = Yii::app()->session;
                $session->open();
                if ($now - (int)@$session['login_time'] > Yii::app()->params['login_interval'])
                {
                    $session['login_tries'] = 0;
                    $session['login_time'] = $now;
                }
                if (@$session['login_tries'] >= $logTries)
                    $model->addError('name', Yii::t('mc', 'Login temporarily blocked.'));
            }
            if(!$model->hasErrors() && $model->validate() && $model->login())
            {
                if ($logTries)
                {
                    $session = Yii::app()->session;
                    $session['login_tries'] = 0;
                    $session['login_time'] = time();
                }
                $url = Yii::app()->user->getReturnUrl(array('server/index', 'my'=>1));
                Yii::log('Successful login: '.$model->name.', forwarding to: '.print_r($url, true));
                $this->redirect($url);
            }
            else if ($logTries)
            {
                $session = Yii::app()->session;
                $session['login_tries'] = @$session['login_tries'] + 1;
                Yii::log('Login failed ('.$session['login_tries'].'/'.$logTries.'): '.$model->name, 'error');
            }
            else
                Yii::log('Login failed: '.$model->name, 'error');

        }
        if ($name)
            $model->name = $name;
        if ($password)
            $model->password = $password;
        $this->render('login',array(
            'model'=>$model,
        ));
    }

    public function actionRegister()
    {
        if (Yii::app()->params['register_disabled'])
            throw new CHttpException(404,Yii::t('mc', 'The requested page does not exist.'));

        $model=new User('register');

        if(isset($_POST['ajax']) && $_POST['ajax']==='register-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        if(isset($_POST['User']))
        {
            $model->attributes=$_POST['User'];
            $model->global_role = 'none';
            if ($model->name == Yii::app()->user->superuser)
                $model->name = '';
            if($model->validate())
            {
                $model->save(false);
                Yii::log('New user registered: '.$model->name.' ('.$model->id.')');
                $this->redirect(array('site/login', 'registered'=>true));
            }
        }
        $this->render('register',array('model'=>$model));
    }

    public function actionLogout()
    {
        Yii::app()->user->logout();
        $this->redirect(Yii::app()->homeUrl);
    }

    public function actionResetPw($l = '', $deactivate = '')
    {
        if (Yii::app()->params['reset_token_hours'] <= 0)
            throw new CHttpException(404, Yii::t('mc', 'The requested page does not exist.'));
        $model = new User;
        $model->unsetAttributes();

        $user = false;
        $hash = false;

        $l = trim(isset($_POST['l']) ? $_POST['l'] : $l);

        if (strlen($l))
        {
            $exp = explode('l', $l);
            $tt = (int)@$exp[0];
            $ll = @$exp[1];
            if (strlen($ll) == 22 && $tt > time())
            {
                $hash = md5($tt.'_'.$ll);
                $model->reset_hash = '='.$hash;
                $prov = $model->search();
                if ($prov->itemCount === 1)
                {
                    $user = $prov->getData();
                    $user = $user[0];
                }
            }
            if (!$hash || !$user || $user->reset_hash !== $hash)
            {
                Yii::app()->user->setFlash('reset-error', Yii::t('mc', 'Invalid password reset token.'));
                $this->redirect(array('site/requestResetPw', 'state'=>'info'));
            }
            if ($deactivate == 'true')
            {
                $user->reset_hash = '';
                if ($user->save())
                {
                    Yii::app()->user->setFlash('reset-success', Yii::t('mc', 'Password reset token deactivated.'));
                    Yii::log('Reset token deactivated');
                }
                else
                    Yii::app()->user->setFlash('reset-error', Yii::t('mc', 'Failed to deactivate password reset token.'));
                $this->redirect(array('site/requestResetPw', 'state'=>'info'));
            }
            if (isset($_POST['User']['password']))
            {
                $user->scenario = 'reset';
                $user->password = $_POST['User']['password'];
                $user->confirmPassword = @$_POST['User']['confirmPassword'];
                $user->reset_hash = '';
                if ($user->save())
                {
                    Yii::log('Password reset!');
                    Yii::app()->user->setFlash('reset-success', Yii::t('mc', 'Your password has been successfully changed.'));
                    $this->redirect(array('site/requestResetPw', 'state'=>'info'));
                }
                else
                {
                    $model->addErrors($user->errors);
                    $model->password = $_POST['User']['password'];
                    $model->confirmPassword = @$_POST['User']['confirmPassword'];
                }
            }
            $model->scenario = 'reset';
        }
            
        $this->render('resetPw', array(
                'model'=>$model,
                'l'=>$l,
            )
        );
    }

    public function actionRequestResetPw($state = '')
    {
        if (Yii::app()->params['reset_token_hours'] <= 0)
            throw new CHttpException(404, Yii::t('mc', 'The requested page does not exist.'));
        $model = new User;
        $model->unsetAttributes();

        if (isset($_POST['User']))
        {
            $state = 'info';
            $user = false;
            if (strlen(@$_POST['User']['email']))
            {
                $model->email = '='.$_POST['User']['email'];
                $prov = $model->search();
                if ($prov->itemCount === 1)
                {
                    $user = $prov->getData();
                    $user = $user[0];
                }
            }
            if (!$user || $user->email !== $_POST['User']['email'])
                Yii::app()->user->setFlash('reset-error', Yii::t('mc', 'No account found for this email address.'));
            else
            {
                $ll = substr(md5($user->id.'_'.time().'_'.rand()), 8, 22);
                $tt = time() + Yii::app()->params['reset_token_hours'] * 3600;
                $l = $tt.'l'.$ll;
                $user->reset_hash = md5($tt.'_'.$ll);
                if (!$user->save(false))
                {
                    Yii::log('Error saving password reset hash for user '.$user->name);
                    Yii::app()->user->setFlash('reset-error', Yii::t('mc', 'Error generating password reset token.'));
                }
                $message = new YiiMailMessage;
                $message->setFrom(array(Yii::app()->params['admin_email']=>Yii::app()->params['admin_name']));
                $message->setTo(array($user->email=>$user->name));
                $message->view = 'resetPw';
                $message->setBody(array(
                    'name'=>$user->name,
                    'l'=>$l,
                    'host'=>Yii::app()->request->getHostInfo(),
                    'panel'=>Yii::app()->request->getBaseUrl(true),
                ));

                Yii::log('Sending password reset email');
                if (!Yii::app()->mail->send($message))
                {
                    Yii::log('Error sending assign password reset link to '.$user->email);
                    Yii::app()->user->setFlash('reset-error', Yii::t('mc', 'Error sending password reset link.'));
                }
                else
                    Yii::app()->user->setFlash('reset-success', Yii::t('mc', 'A password reset link has been sent to your email address.'));
            }
            $this->redirect(array('site/requestResetPw', 'state'=>'info'));
        }
        $this->render('requestResetPw', array(
                'model'=>$model,
                'state'=>$state,
            )
        );
    }
}
