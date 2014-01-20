<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
class Controller extends CController
{
    public $layout='//layouts/column2';
    public $menu=array();
    public $breadcrumbs=array();
    public $ajaxUpdateInterval=2000;

    public $notice = '';

    public $version = '1.8.2-64';

    public function init()
    {
        parent::init();

        // Subset of http://detectmobilebrowsers.com/
        if(preg_match('/android.+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|'
            .'hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i'
            .'|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone'
            .'|wap|windows (ce|phone)|xda|xiino/i', Yii::app()->request->userAgent))
        {
            Yii::app()->theme = 'mobile';
        }


        $pf = @$_REQUEST['mc_platform'];
        $pf = $pf ? $pf : @$_SESSION['mc_platform'];
        if ($pf && preg_match('/^[-_\\w\\d]{2,16}$/', $pf))
        {
            Yii::app()->params['platform'] = $pf;
            Yii::app()->theme = 'platform'.DIRECTORY_SEPARATOR.$pf;
        }

        Yii::app()->widgetFactory->widgets['CGridView']['cssFile']      = Theme::css('gridview.css');
        Yii::app()->widgetFactory->widgets['CDetailView']['cssFile']    = Theme::css('detailview.css');
        Yii::app()->widgetFactory->widgets['CDetailView']['itemTemplate'] =
            "<tr class=\"{class}\"><th>{label}</th><td>{value}</td><td class=\"hintRow\">{hint}</td></tr>\n";
        Yii::app()->widgetFactory->widgets['CLinkPager']['cssFile']     = Theme::css('pager.css');

        if (@Yii::app()->params['installer'] === 'show'
            || (Yii::app()->params['superuser_check_only'] && !Yii::app()->user->isSuperuser()))
            return;

        $checkKey = 'mcUpdateCheck';
        $noticeKey = 'mcUpdateNotice';

        $last = (int)Yii::app()->user->getState($checkKey, 0);
        $this->notice = Yii::app()->user->getState($noticeKey, '');
        if (!($check = (time() - $last) >= 3600)) //Check every hour
            return;

        Yii::app()->user->setState($checkKey, time());
        try
        {
            $s = Setting::model()->findByPk('updateChecks');
            if ($s && ($s->value == '0' || $s->value == 'False' || $s->value == 'false'))
                $check = false; 
        }
        catch(Exception $e) {}

        if (!$check)
            return;

        $context = stream_context_create(array(
            'http' => array(
                'timeout' => 5,
                'method' => 'POST',
                'header' =>
                    'Content-Type: application/x-www-form-urlencoded'."\r\n"
                    .'Referer: '.Yii::app()->request->getBaseUrl(true)."\r\n",
                'content' => http_build_query(array('version'=>$this->version)),
            )
        ));
        $remote = @file_get_contents('http://www.multicraft.org/site/version?panel=true', 0, $context);

        $pattern = '[\w]?([\d]+)\.([\d]+)\.([\d]+)(-(.*))?';
        $ml = array();
        $mr = array();
        preg_match('/'.$pattern.'/', $this->version, $ml);
        preg_match('/<v>'.$pattern.'<\/v>/', $remote, $mr);

        if (count($ml) < 3)
            $this->notice = '';
        else if (count($mr) < 3)
            $this->notice = 'Update check failed';
        else if ($mr[1] > $ml[1] 
            || ($mr[1] == $ml[1] && $mr[2] > $ml[2])
            || ($mr[1] == $ml[1] && $mr[2] == $ml[2] && $mr[3] > $ml[3])
            || ($mr[1] == $ml[1] && $mr[2] == $ml[2] && $mr[3] == $ml[3]
                && (count($ml) >= 6 && preg_match('/^pre/', $ml[5])))
            )
        {
            $this->notice = Yii::t('mc', 'Update available')
                .': <a href="http://www.multicraft.org/"><b>'.$remote.'</b></a>';
        }
        else
            $this->notice = '';
        Yii::app()->user->setState($noticeKey, $this->notice);
    }

    /**
     * Helper functions
     */
    public function printRefreshScript($callback = False)
    {
        if (Yii::app()->params['ajax_updates_disabled'] === true)
            return;
    ?>
<script type="text/javascript">
/*<![CDATA[*/
    function refresh(type)
    {
        <?php echo CHtml::ajax(array('type'=>'POST', 'dataType'=>'json',
                'success'=>'js:(type == "all") ? set_data : $.noop()', 'data'=>array('ajax'=>'refresh', 'type'=>'js:type',
                    Yii::app()->request->csrfTokenName=>Yii::app()->request->csrfToken,
                )
            )) ?>

    }
    function set_data(data)
    {
        for (var key in data)
        {
            fn = window['set_' + key + '_data'];
            if (typeof fn == 'function')
                fn(data);
            else
            {
                var elem = $('#' + key + '-ajax');
                if (elem.is('select'))
                {
                    prevVal = elem.val();
                    var scroll = elem[0].scrollHeight;
                    elem.html(data[key]);
                    if (prevVal)
                        elem.val(prevVal);
                    else
                        elem.val($('#' + key + '-ajax option').val());
                    elem[0].scrollHeight = scroll;
                }
                else if(elem.is('textarea'))
                {
                    var scroll = elem[0].scrollTop;
                    elem.val(data[key]);
                    if ($.browser.msie)
                    {
                        setTimeout(function() {
                            elem[0].scrollTop = scroll;
                        }, 10);
                    }
                    else
                        elem[0].scrollTop = scroll;
                }
                else
                    elem.html(data[key]);
            }
        }
        <?php echo ($callback ? $callback.'();' : '') ?>        
        scheduleRefresh();
    }
    function scheduleRefresh()
    {
        setTimeout(function() { refresh('all'); }, <?php echo $this->ajaxUpdateInterval ?>);
    }
    $(document).ready(function() {
        scheduleRefresh();
    });

    function kickPlayer(id)
    {
        <?php echo CHtml::ajax(array('type'=>'POST', 'data'=>array('ajax'=>'kick', 'player'=>'js:id',
                Yii::app()->request->csrfTokenName=>Yii::app()->request->csrfToken,),
            'success'=>'function(e) {if (e) alert(e);}')) ?>
    }
/*]]>*/
</script>
    <?php
    }

    function img($name)
    {
        return Yii::app()->baseUrl.'/images/'.$name.'.png';
    }


    function listLanguages()
    {
        $msgDir = dirname(__FILE__).'/../messages/';
        $dir = @opendir($msgDir);

        $langInfo = array();
        if (!$dir)
            return $langInfo;

        while($lang = readdir($dir))
        {
            $li = @include($msgDir.$lang.'/name.php');
            if ($li)
                $langInfo[$lang] = $li;
            else if (is_dir($msgDir.$lang) && !in_array($lang, array('.', '..', 'en')))
                $langInfo[$lang] = array('short'=>$lang, 'english'=>$lang, 'local'=>$lang);
        }
            
        closedir($dir);
        return $langInfo;
    }

    function languageSelection()
    {
        $li = $this->listLanguages();
        $eng = Yii::t('mc', 'English');
        $sel = array();
        if (@count($li) && is_array($li))
        {
            foreach ($li as $k=>$l)
                $sel[$k] = $l['local'] ? $l['local'] : $k;
        }
        natcasesort($sel);
        return array(''=>Yii::t('mc', 'Default'), 'en'=>'English') + $sel;
    }

    function listThemes()
    {
        $msgDir = dirname(__FILE__).'/../../themes/';
        $dir = @opendir($msgDir);

        $themeInfo = array();
        if (!$dir)
            return $themeInfo;

        while($theme = readdir($dir))
        {
            $ti = @include($msgDir.$theme.'/name.php');
            if ($ti)
                $themeInfo[$theme] = $ti;
            else if (is_dir($msgDir.$theme) && !in_array($theme, array('.', '..', 'mobile', 'platform')))
                $themeInfo[$theme] = $theme;
        }
            
        closedir($dir);
        return $themeInfo;
    }

    function themeSelection()
    {
        $ti = $this->listThemes();
        $sel = array();
        if (@count($ti) && is_array($ti))
        {
            foreach ($ti as $k=>$l)
                $sel[$k] = $l ? $l : $k;
        }
        natcasesort($sel);
        return array(''=>Yii::t('mc', 'Default')) + $sel;
    }
}
