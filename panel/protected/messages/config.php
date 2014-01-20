<?php
/**
 * This is the configuration for generating message translations
 * for the Yii framework. It is used by the 'yiic message' command.
 */
return array(
    'sourcePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    'messagePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'messages',
    'languages'=>array('de'),
    'fileTypes'=>array('php'),
    'overwrite'=>true,
    'exclude'=>array(
        'CVS',
        '.hg',
        '.svn',
        'yiilite.php',
        'yiit.php',
        '/data',
        '/extensions',
        '/i18n/data',
        '/messages',
        '/runtime',
        '/tests',
        '/vendors',
        '/web/js',
        '/yii',
    ),
);
