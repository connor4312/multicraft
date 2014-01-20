<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class PlayerController extends Controller
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
                'actions'=>array('view'),
                'users'=>array('*'),
            ),
            array('allow',
                'actions'=>array('index', 'create', 'update', 'delete',),
                'users'=>array('@'),
            ),
            array('allow',
                'actions'=>array('admin',),
                'expression'=>'$user->isSuperuser()',
            ),
            array('deny',
                'users'=>array('*'),
            ),
        );
    }

    public function ajaxRefresh($player, $type)
    {
        $all = ($type === 'all');
        if (!is_array($type))
            $type = array($type);
        $ret = array();

        if ($all || in_array('tp', $type) || in_array('summon', $type))
        {
            ob_start();
            $error = '';
            if (!Yii::app()->user->can($player->server_id, 'get players'))
                $error = Yii::t('mc', 'Permission denied.');
            else if (!McBridge::get()->serverCmd($player->server_id, 'get players', $players))
                $error = McBridge::get()->lastError();

            if (strlen($error))
                echo CHtml::encode($error);
            else
            {
                foreach ($players as $player)
                    echo CHtml::tag('option', array('value'=>$player['id'], (($player['id'] == @$defOther) ? 'selected' : '')=>'selected'), CHtml::encode($player['name']));
            }
            $ret['summon'] = $ret['tp'] = ob_get_clean();
        }
        return $ret;
    }

    private function possiblePlayerRoles($model)
    {
        $userRoles = array('1' => Yii::t('mc', 'Default Role'));
        $userRoles += array_combine(User::$roleLevels, User::getRoleLabels());
        if (!Yii::app()->user->isSuperuser())
        {
            $idx = array_search(Yii::app()->user->serverRole($model->server_id), User::$roles);
            if ($idx === false)
                $userRoles = array();
            else
            {
                $ur = $userRoles;
                $userRoles = array();
                foreach ($ur as $level=>$role)
                {
                    $userRoles[$level] = $role;
                    if (--$idx < 0)
                        break;
                }
            }   
            if (array_search($model->level, array_keys($userRoles)) === false)
                $userRoles = array();
        }
        return $userRoles;
    }

    public function getUserCondition($id)
    {
        $cond = array();
        if ((Yii::app()->params['hide_userlist'] === true) && !Yii::app()->user->isSuperuser())
        {
            $cond = array('condition'=>'(select role from user_server where server_id='
                .((int)$id).' and user_id=id) != \'\'');
        }
        $cond['order'] = 'name asc';
        return $cond;
    }

    public function actionView($id)
    {
        $model = $this->loadModel($id);
        Yii::app()->user->canSelf($model, 'view player', true);
        if (isset($_POST['ajax']))
        {
            switch($_POST['ajax'])
            {
            case 'give':
                if (!Yii::app()->user->canSelf($model, 'give', true))
                    die(Yii::t('mc', 'Error: Permission denied.'));
                require 'itemlist.php';
                $amount = (int)$_POST['amount'];
                $item = (int)$_POST['item'];

                if ($item < 0 || $item >= count($itemlist))
                    die("Invalid item selected!");
                else if ($amount < 0 || $amount > 64)
                    die("Invalid amount!");
                else
                {
                    $itemcode = $itemlist[$item]['item'];
                    if (!McBridge::get()->serverCmd($model->server_id,
                            'mc:give '.$model->name.' '.$itemcode.' '.$amount))
                        die(Yii::t('mc', 'Error sending command:').' '.CHtml::encode(McBridge::get()->lastError()));
                }
                break;
            case 'tp':
                Yii::app()->user->canSelf($model, 'tp', true);
                $player = (int)$_POST['player'];
                $player = Player::model()->findByPk($player);
                if (!$player)
                    die(Yii::t('mc', 'Invalid target player...'));
                if (!McBridge::get()->serverCmd($model->server_id, 'mc:tp '.$model->name.' '.$player->name))
                    die(Yii::t('mc', 'Error sending command:').' '.CHtml::encode(McBridge::get()->lastError()));
                break;
            case 'summon':
                Yii::app()->user->can($model->server_id, 'summon', true);
                $player = (int)$_POST['player'];
                $player = Player::model()->findByPk($player);
                if (!$player)
                    die(Yii::t('mc', 'Invalid target player...'));
                if (!McBridge::get()->serverCmd($model->server_id, 'mc:tp '.$player->name.' '.$model->name))
                    die(Yii::t('mc', 'Error sending command:').' '.CHtml::encode(McBridge::get()->lastError()));
                break;
            case 'assign':
                $user = (int)$_POST['user'];
                $user = User::model()->findByPk($user);
                if (!$user)
                    die(Yii::t('mc', 'Invalid user selected.'));
                if (!($model->user = $user))
                    die(Yii::t('mc', 'Failed to assign user!'));
                break;
            case 'refresh':
                echo CJSON::encode($this->ajaxRefresh($model, $_POST['type']));
            }
            Yii::app()->end();
        }

        $playerRoles = $this->possiblePlayerRoles($model);
        if(isset($_POST['Player']) && Yii::app()->user->can($model->server_id, 'manage players'))
        {
            if (Yii::app()->user->isSuperuser())
                $model->scenario = 'superuser';
            $lvl = $model->level;
            $model->attributes=$_POST['Player'];
            if ($playerRoles)
            {
                if (array_search($model->level, array_keys($playerRoles)) === false)
                    $model->level = $lvl; //preserve level if user has no permission to change it
                $user = (int)@$_POST['user-assign'];
                $userModel = User::model()->findByPk($user);
                if ($userModel && $model->user != $user
                        && array_search($userModel->getLevel($model->server_id), array_keys($playerRoles)) !== false)
                {
                    Yii::log('Assigning user '.$user.' to player '.$model->id);
                    $model->setUser($user, false);
                }
                else if (!$user && $model->user && isset($_POST['user-assign']))
                {
                    Yii::log('Removing user assignment from player '.$model->id);
                    $model->setUser(0, false);
                }
                    
            }
            if($model->save())
            {
                Yii::log('Updated player '.$model->id);
                Yii::app()->user->setFlash('player', Yii::t('mc', 'Player saved.'));
                $this->redirect(array('view','id'=>$model->id));
            }
        }
    
        $users = User::model()->findAll($this->getUserCondition($model->id));

        require 'itemlist.php';
        $edit = @count($playerRoles) && Yii::app()->user->can($model->server_id, 'manage players');
        $this->render('view',array(
            'model'=>$model,
            'itemlist'=>$itemlist,
            'viewDetails'=>Yii::app()->user->can($model->server_id, 'player details'),
            'summon'=>Yii::app()->user->canSelf($model, 'summon'),
            'give'=>Yii::app()->user->canSelf($model, 'give'),
            'tp'=>Yii::app()->user->canSelf($model, 'tp'),
            'edit'=>$edit,
            'sv'=>$model->server_id,
            'data'=>$this->ajaxRefresh($model, 'all'),
            'playerRoles'=>$playerRoles,
            'users'=>$users,
        ));
    }

    public function actionCreate($sv = false)
    {
        if (!Yii::app()->user->isSuperuser()
            && !Yii::app()->user->can($sv, 'manage players', true))
            Yii::app()->user->deny();

        $server = Server::model()->findByPk((int)$sv);
        $model = new Player;
        $model->server_id = $sv;
        $model->level = 1;

        $playerRoles = $this->possiblePlayerRoles($model);
        if(isset($_POST['Player']))
        {
            if (Yii::app()->user->isSuperuser())
                $model->scenario = 'superuser';
            $model->attributes=$_POST['Player'];
            if (array_search($model->level, array_keys($playerRoles)) === false)
                $model->level = 0; //no permission
            $user = (int)@$_POST['user-assign'];
            $userModel = User::model()->findByPk($user);
            if ($userModel && array_search($userModel->getLevel($sv), array_keys($playerRoles)) === false)
                $user = 0;
            if($model->save())
            {
                Yii::log('Created player '.$model->id);
                if ($model->user != $user)
                    $model->user = $user;
                $this->redirect(array('view','id'=>$model->id));
            }
        }

        $users = User::model()->findAll($this->getUserCondition($model->id));
        $this->render('view',array(
            'model'=>$model,
            'sv'=>$sv,
            'edit'=>true,
            'playerRoles'=>$playerRoles,
            'users'=>$users,
        ));
    }

    public function actionDelete($id)
    {
        if(Yii::app()->request->isPostRequest)
        {
            $model = $this->loadModel($id);
            $sv = $model->server_id;
            Yii::app()->user->can($model->server_id, 'manage players', true);
            Yii::log('Deleting player '.$model->id);
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
        $model=new Player(Yii::app()->user->isSuperuser() ? 'search' : 'serverSearch');
        $model->unsetAttributes();
        $model->server_id = $sv;
        if(isset($_GET['Player']))
            $model->attributes=$_GET['Player'];

        $this->render('index',array(
            'model'=>$model,
            'sv'=>$sv,
        ));
    }

    public function actionAdmin()
    {
        $model=new Player('search');
        $model->unsetAttributes();
        if(isset($_GET['Player']))
            $model->attributes=$_GET['Player'];

        $this->render('admin',array(
            'model'=>$model,
        ));
    }

    public function loadModel($id)
    {
        $model=Player::model()->findByPk((int)$id);
        if($model===null)
            throw new CHttpException(404,Yii::t('mc', 'The requested page does not exist.'));
        return $model;
    }

    protected function performAjaxValidation($model)
    {
        if(isset($_POST['ajax']) && $_POST['ajax']==='player-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
