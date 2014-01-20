<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

$this->renderPartial('db_common', array('p'=>$p, 'type'=>'panel', 'desc'=>'Panel'));
?>
<br/>
<b>Database description</b><br/>
<br/>
The panel database stores user information and control panel specific server settings. It is only used by the control panel and not critical for the daemons to run.<br/>
