<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/

class PluginFilter extends CModel
{
    public $file;
    public $desc;
    public $status;

    private $filter;

    public function attributeNames()
    {
        return array('file', 'desc', 'status');
    }

    public function rules()
    {
        return array(
            array('file, desc, status', 'safe'),
        );
    }

    public function prepareFilters()
    {
        foreach ($this->attributes as $k=>$v)
        {
            if (!strlen($v))
                continue;
            if (@$v[0] == '=')
            {
                $v = substr($v, 1);
                $this->filter[$k]['t'] = 'match';
            }
            else if (strlen($v) > 1 && substr($v, 0, 2) == '<>')
            {
                $v = substr($v, 2);
                $this->filter[$k]['t'] = 'not_in';
            }
            else
                $this->filter[$k]['t'] = 'in';
            $this->filter[$k]['v'] = $v;
        }
    }

    public function filter($p)
    {
        foreach ($p as $k=>$v)
        {
            $f = @$this->filter[$k];
            if (!$f)
                continue;
            switch ($f['t'])
            {
            case 'match':
                if ($v != $f['v'])
                    return false;
                break;
            case 'not_in':
                if (strlen($v) && stristr($v, $f['v']))
                    return false;
                break;
            default:
                if (stristr($v, $f['v']) === false)
                    return false;
            }
        }
        return true;
    }
}
