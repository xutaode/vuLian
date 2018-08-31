<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/7/30
 * Time: 9:43
 */

namespace app\admin\controller;
use think\Db;
use think\Exception;
/**
 * 返佣
 * Class RebateController
 * @package app\admin\controller
 */
class RebateController extends Common
{
    /**
     * 修改返佣数量
     * @return \think\response\Json
     */
    function upRebateNumber($data = false){
        $user = $data ? $data: '1001';
        $param = $data ? $data: input("post.");
        $ReturnType = $data? 'array':'json';

        //  1 一键获取
        if ($param["type"] == 1){
            $where=  "User_Num = '".$user."' and State = 0";
        }elseif ($param["type"] == 2){
            $where= "By_User_Num = '".$param["user"]."' and State = 0";
        }
        Db::startTrans();
        try{
            $number = model("Rebate")->getNmber($where);
            if ($number > 0){
                if (model("Rebate")->UpNumber($where) <= 0)
                    return inputOut("修改返佣数量失败",'0001',"",$ReturnType,true);
                //  修改用户余额
                $info = model("User")->getUserList(['User_Num'=>$user])[0];
                if(model("User")->upUser(['User_Num'=>$user, 'State'=>1], ['Stone'=> $info['Stone'] + $number]) <= 0)
                    return inputOut("修改用户余额失败",'0001',"",$ReturnType,true);
                Db::commit();
            }
            return inputOut("",'',"",$ReturnType);
        }catch (Exception $e){
            Db::rollback();
            return inputOut("代码异常", '0001', $e->getMessage(),$ReturnType);
        }
    }
}