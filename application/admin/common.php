<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/** 权限列表数组处理
 * @param $list
 * @param int $pid
 * @param int $show
 * @return array
 */
function  GetListArray($list,$pid=0,$show=0)
{
    $prefix = "/static/";
    $result = array();
    foreach ($list as $k => $v)
    {
        if ($v['Pid'] == $pid && (($show==1) || ($show==0 && $v['IsShow'] == 1))  )
        {
            $v['subset'] = GetListArray($list,$v['Id'],$show);
            $v['href'] = ($v['Pid'] != 0) ? $prefix.$v['href'] : $v['href'];
            if ($v['subset'] == null) {unset($v['subset']);}
            if ($v['IsShow'] && $show==0) {unset($v['IsShow']);}
            unset($v['Pid']);unset($v['Id']);unset($v['Type']);
            $result[] = $v;
        }
    }
    return $result;
}
