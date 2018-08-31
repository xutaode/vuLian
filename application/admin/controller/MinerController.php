<?php
/**
 * Created by PhpStorm.
 * User: 徐涛
 * Date: 2018/7/26
 * Time: 22:06
 */

namespace app\admin\controller;
/**
 * 矿机管理
 * Class Miner
 * @package app\admin\controller
 */
class MinerController extends Common
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
     * 获取矿场列表
     * @return array
     */
    function getProjectList(){
        return inputOut("",'',model("Project")->getProjectList());
    }

    /**
     * 获取矿机等级列表
     * @return array
     */
    function getMiningLeaveList(){
        return inputOut("",'',model("Mining")->getMining());
    }

    /**
     * 获取矿机列表
     * @return array
     */
    function getMiningList(){
        return inputOut("",'',model("MiningList")->getMiningList());
    }

    /**
     * 选择矿机
     * @param $user
     * @param $millId
     * @param $type  edit 修改   add 添加  stop停止  remove 移除
     */
    function chooseMill($user, $millId, $type='edit'){
        if ($type == "edit" || $type == "stop" || $type == "remove"){
			$userInfo = model("User")->getUserList(['User_Num' => $user, 'State' => 1])[0];
            $Millstate = false; //  矿机的状态
            if ($type != "remove"){
                // 有没正在使用的矿机
                $MillList = model("MiningList")->getMiningList(['User_Num'=>$user, 'State'=>0]);
                if (!empty($MillList)){
                    $result = $MillList[0];
                    if (model("MiningList")->updateMiningList(['Id'=>$result['Id']], ['State'=>1]) <= 0)
                        return "停止矿机失败";
                    $Hashrate = $userInfo['Hashrate'] - $result['Hashrate'];
                    $time = $userInfo['Mining_Minute'] + $result['Time'];
                    $Millstate = true;
                }else{
                    $Hashrate = $userInfo['Hashrate'];
                    $time = $userInfo['Mining_Minute'];
                    if ($type == "stop") return "没有正在运行的矿机";
                }

                if ($type == "edit"){
                    $MillList = model("MiningList")->getMiningList(['Id'=>$millId, 'State'=>1])[0];
                    if (model("MiningList")->updateMiningList(['Id'=>$MillList['Id']], ['State'=>0]) <= 0)
                        return "开启矿机失败";
                    $Millstate = true;
                    $value = $Hashrate + $MillList['Hashrate'] ;
                    $timeValue = $time - $MillList['Time'] ;
                }else{
                    $value = $Hashrate;
                    $timeValue = $time;
                }
            }else{
                if (model("MiningList")->updateMiningList(['Id'=>$millId['Id']], ['State'=>2]) <= 0)
                    return "移除矿机失败";
                if ($millId['State'] == 0) $Millstate = true;
                $value = $userInfo['Hashrate'] - $millId['Hashrate'];
                $timeValue = $userInfo['Mining_Minute'] + $millId['Time'];
            }
            if ($Millstate) return $this->upHashrate($user, $value, $timeValue);
        }else{
            return "请重新选择矿机操作";
        }
        return false;
    }

    /**
     * 修改算力
     * @param $user
     * @param $value
     * @param $timeValue
     * @param $type 是否需要计算
     * @return string
     */
    function upHashrate($user, $value=0, $timeValue=0, $type=false){
		cache($user."_mining_index", null);
        $userInfo = cache($user."_mining_index");
        $lineState = true;
        if (!$userInfo) {
            $lineState = false;
            $userInfo = model("User")->getUserList(['User_Num' => $user, 'State' => 1])[0];
        }
        if ($type){
            $value += $userInfo['Hashrate'];
            $timeValue += $userInfo['Mining_Minute'];
        }
        if(model("User")->upUser(['User_Num'=>$user], ['Hashrate'=>$value, 'Mining_Minute'=>$timeValue]) < 0)
            return "修改用户算力失败";
        if (model("Schedule")->upSchedule(['UserNum'=>$user],['Hashrate'=>$value, 'Mining_Minute'=>$timeValue]) < 0)
            return "修改发币表数据失败";

        if ($lineState){
            $userInfo['Hashrate'] = $value;
            $userInfo['Mining_Minute'] = $timeValue;
            cache($user."_mining_index", $userInfo);
        }
        return false;
    }
}