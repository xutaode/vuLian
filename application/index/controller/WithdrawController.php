<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/8/1
 * Time: 18:28
 */

namespace app\index\controller;
use think\Db;
use think\Exception;


/**
 * 提现
 * Class WithdrawController
 * @package app\index\controller
 */
class WithdrawController extends baseController
{
    /**
     * 获取提现列表
     * @return array
     */
    function getWithList(){
        $page = input("get.page");
        $limit = input("get.limit");
        $limitparam = $limit ? [$page, $limit] : false;
        $sizeData = model("Withdraw")->countWithdrawList(['User_Num' => $this->userNum]);
        $maxpage = ceil($sizeData/$limit);
        return inputOut("","",model("Withdraw")->getWithdrawList(['User_Num'=>$this->userNum],false,$limitparam),"",$maxpage, $sizeData);
    }

    /**
     * 提现申请
     * @return array
     */
    function addWith(){
        try{
            $data = input("post.");
            MyLog("提现申请请求：", $data);

            $address = model("User")->getUserList(['address'=>$data['address'],'State'=>1]);
            if (!$address)
                exception("没有此地址信息",1111);

            $userCache = cache($this->userNum."_mining_index");
            if ($userCache['address'] == $address[0]['address'])
                $userInfo = $address[0];
            else
                $userInfo = model("User")->getUserList(['User_Num'=>$this->userNum,'State'=>1])[0];

            if ($userInfo['Stone'] < $data['number'])
                exception("可用宝石不足",1111);

            Db::startTrans();
            $insert = array(
                'Order_Num'     =>createdCode('order'),
                'User_Num'     =>$userInfo['User_Num'],
                'Address'     =>$userInfo['address'],
                'Number'     =>$data['number'],
                'Sbreak'     =>'用户提现',
            );
            if (model("Withdraw")->addWithdraw($insert) < 0)
                exception("添加记录失败",1111);
            $number = $userInfo['Stone'] - $data['number'];
            if (model("User")->upUser(['User_Num'=>$this->userNum,'State'=>1],['Stone'=>$number]) < 0)
                exception("修改宝石失败",1111);

            //  添加日志
            model("UserLog")->addUserLog([
                'User_Num'              =>  $userInfo['User_Num'],
                'Log'                   =>   "用户提现，地址".$userInfo['address'],
                'Type'                  =>  2,
                'Stone'                 =>  $userInfo['Stone'],
                'Frozen_Stone'          =>  $userInfo['Frozen_Stone'],
                'Change_Stone'          =>   '-'.$data['number'],
                'Change_Frozen_Stone'  =>  0,
            ]);

            Db::commit();
            return inputOut();
        }catch (Exception $e){
            Db::rollback();
            $msg = ($e->getCode() == 1111) ? $e->getMessage() :'代码异常';
            $code = ($e->getCode() == 1111) ? '0001' :'9999';
            if ($code == '9999')
                MyLog("提现申请返回：", $e->getMessage()."-//////-".$e->getTraceAsString());
            else
                MyLog("提现申请返回：", $e->getMessage());
            return inputOut($msg,$code,$e->getMessage());
        }
    }
}