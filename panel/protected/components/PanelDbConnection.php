<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class PanelDbConnection extends DbConnection
{
    public function init()
    {
        $this->version = 10;
        $this->type = 'panel';
        parent::init();
    }
}
