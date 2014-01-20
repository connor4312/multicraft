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
        'import'=>array(
            'application.controllers.ApiController',
        ),
        'components'=>array(
            'user'=>array(
                'class'=>'WebUser',
                'allowAutoLogin'=>false,
            ),
            'urlManager'=>array(
                'rules'=>array(
                ),
            ),
            'errorHandler'=>array(
                'errorAction'=>'api/error',
            ),
            'log'=>array(
                'class'=>'CLogRouter',
                'routes'=>array(
                    array(
                        'class'=>'CFileLogRoute',
                        'levels'=>'info, error, warning',
                        'logFile'=>'api.log',
                    ),
                ),
            ),
        ),
    )
);


