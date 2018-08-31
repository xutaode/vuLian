<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/8/2
 * Time: 14:33
 */

namespace app\index\controller;
use think\Db;
use think\Exception;

/**
 * 礼包
 * Class GiftController
 * @package app\index\controller
 */
class GiftController extends baseController
{
    /**
     * 获取礼包列表
     * @return array
     */
    function getGiftList(){
        return inputOut("","",model("Gift")->getGift());
    }

    /**
     * 购买礼物
     * @return array
     */
    function buyGift(){
        try{
            $data = input("post.");
            MyLog("购买礼包请求：", $data);
            $userInfo = model("User")->getUserList(['User_Num'=>$this->userNum,'State'=>1])[0];

            Db::startTrans();

            $gift = model("Gift")->getGift(['Id'=>$data['id'],'State'=>1]);
            if (!$gift) exception("没有该礼包信息",1111);else $gift = $gift[0];
            $inset = [
                'GiftId'    =>$gift['Id'],
                'User_Num'    =>$userInfo['User_Num'],
            ];
            if (model("GiftRecord")->addGiftRecord($inset) < 0) exception("添加记录失败",1111);
            $number = $userInfo['Stone'] + $gift['Stone'];
            if (model("User")->upUser(['User_Num'=>$this->userNum,'State'=>1],['Stone'=>$number]) < 0)
                exception("修改宝石失败",1111);

            //  添加日志
            model("UserLog")->addUserLog([
                'User_Num'              =>  $userInfo['User_Num'],
                'Log'                   =>   "购买礼包",
                'Type'                  =>  2,
                'Stone'                 =>  $userInfo['Stone'],
                'Frozen_Stone'          =>  $userInfo['Frozen_Stone'],
                'Change_Stone'          =>  $gift['Stone'],
            ]);

            Db::commit();
            return inputOut();
        }catch (Exception $e){
            Db::rollback();
            $msg = ($e->getCode() == 1111) ? $e->getMessage() :'代码异常';
            $code = ($e->getCode() == 1111) ? '0001' :'9999';
            if ($code == '9999')
                MyLog("购买礼包返回：", $e->getMessage()."-//////-".$e->getTraceAsString());
            else
                MyLog("购买礼包返回：", $e->getMessage());
            return inputOut($msg,$code,$e->getMessage());
        }
    }

}