<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/7/30
 * Time: 12:12
 */

namespace app\index\controller;
use app\admin\controller\MinerController;
use app\common\service\ShortMessage;

/**
 * 任务
 * Class TaskController
 * @package app\index\controller
 */
class TaskController extends baseController
{
    /**
     * 获取算力任务
     * @return array
     */
    function getList(){
        $data = [
            'list'=> model("HashrateTask")->getHashrateTaskList(),
            'task'=> cache($this->userNum."_mining_index")['Task'],
        ];
        return inputOut("","",$data);
    }

    /**
     * 检测任务
     * @return bool|string
     */
    function inspectTask($user = '1049',$type='User'){
        $task = FromFieldGetArray(model("HashrateTask")->getHashrateTaskList(),'TypeCode');
        //  推荐任务
        if ($type == 'User'){
            $result = $this->tuiJian($user, $task[$type]);
            if ($result) return $result;
        }
        return false;
    }

    /**
     * 推荐任务
     * @param $user
     * @param $list
     * @return bool|string
     */
    function tuiJian($user, $list){
        $userInfo = cache($user."_mining_index");
        $count = model("User")->countUser(['Acp_User_Num'=>$userInfo['User_Num']]);
        $miner = new  MinerController();
        foreach ($list as $k => $v){
            if (!in_array($v['Id'], $userInfo['task'])){
                if ($v['Value'] <= $count){
                    $userInfo['task'] .= $v['Id'].",";
                    $userInfo['Hashrate'] += $v['Hashrate'];
                    if ( model("User")->upUser(['User_Num'=>$userInfo['User_Num']], ['task'=>$userInfo['task']]) ) return "修改任务列表失败";
                    $result = $miner->upHashrate($userInfo['User_Num'],$v['Hashrate']);
                    if ($result) return $result;
                }
            }
        }
        cache($userInfo['User_Num']."_mining_index", $userInfo);
        return false;
    }
}