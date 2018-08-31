<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/7/30
 * Time: 10:07
 */

namespace app\admin\controller;

/**
 * 挖矿进度
 * Class ScheduleController
 * @package app\admin\controller
 */
class ScheduleController extends Common
{
    /**
     * 初始化
     * MinerController constructor.
     * @param \think\Request $flg
     */
    function __construct($flg=false)
    {
        parent::_initialize($flg);
    }

    /**
     * 修改挖矿进度
     * @param $user
     * @param int $count
     * @return array
     */
    function upSchedule($user,$count=0){
        $Schedule = model("Schedule")->getScheduleList([
            'UserNum'=>$user,
            'prjectId'=>1
        ])[0];
        $data = array(
            'Amount'    =>  $Schedule['Amount'] - $count,
        );
        if ($Schedule['State'] != 1){
            $data['State'] = 1;
            $data['NextTime'] = date("Y-m-d H:i:s", strtotime("+30 minute"));
        }
        if (model("Schedule")->upSchedule(['UserNum' => $user], $data) < 0)
            return "修改挖矿进度失败";
        return false;
    }

}