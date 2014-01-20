<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class DaemonDbConnection extends DbConnection
{
    public function init()
    {
        $this->version = 10;
        $this->type = 'daemon';
        parent::init();
    }
}
