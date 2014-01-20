<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

return CMap::mergeArray(
    require(dirname(__FILE__).'/application.php'),
    array(
        'components'=>array(
            'urlManager'=>array(
                'rules'=>array(
                ),
            ),
        ),
    )
);


