<?php

namespace app\common\model;
use \think\Model;

class SysLog extends Model
{
    /** 记录日志
     * @param $name
     * @param $info
     */
    function addLog($name, $info){
        $this::insert(array(
            'User'=>$name,
            'Msg'=>$info,
            'Add_Time'=>date("Y-m-d H:i:s"),
            'Ip'=>request()->ip(),
        ));
    }
}