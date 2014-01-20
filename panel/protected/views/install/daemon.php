<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

$this->renderPartial('db_common', array('p'=>$p, 'type'=>'daemon', 'desc'=>'Daemon'));
?>
<br/>
<b>Database description</b><br/>
<br/>
The daemon database stores all the data required by the Multicraft daemon. If multiple daemons are controlled from the same control panel, all the daemons share the same database. In such a scenario the database is required to be a MySQL database and it must be accessible from the machines running the daemon.<br/>
This database is critical for the daemon to work correctly. The panel and daemon database can be the same but it's recommended to separate them.<br/>
<br/>
