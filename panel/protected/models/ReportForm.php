<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class ReportForm extends CFormModel
{
    public $name;
    public $email;
    public $report;
    public $verifyCode;

    public function rules()
    {
        return array(
            array('report', 'required'),
            array('email', 'required'),
            array('email', 'email'),
            array('name, report, email', 'safe'),
            array('verifyCode', 'captcha', 'allowEmpty'=>!CCaptcha::checkRequirements()),
        );
    }

    public function attributeLabels()
    {
        return array(
            'name'=>Yii::t('mc', 'Name'),
            'email'=>Yii::t('mc', 'Email'),
            'report'=>Yii::t('mc', 'Message'),
            'verifyCode'=>Yii::t('mc', 'Verification Code'),
        );
    }
}
