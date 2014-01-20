<?php
/**
 *
 *   Copyright © 2010-2012 by http://www.xhost.ch (Daniel Hofer)
 *
 *   All rights reserved.
 *
 **/

$this->redirect(array('server/index', 'my'=>(Yii::app()->user->isSuperuser() ? 0 : 1)));
