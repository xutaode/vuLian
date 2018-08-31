<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/7/27
 * Time: 10:55
 */
namespace app\index\controller;
use app\admin\controller\MinerController;
use app\admin\controller\ScheduleController;
use think\Db;
use think\Exception;

/**
 * 前台矿操作
 * Class Mining
 * @package app\index\controller
 */
class MiningController extends baseController
{
    /**
     * 获取用户挖矿记录
     * @return \think\response\Json
     */
    function getRecord(){
        $MillRecord  = model("MillRecord");
        $result['list'] = $MillRecord->getMillRecord([
            'User_Num'=>$this->userNum,
            'State' =>1,
        ]);
        $result['Number'] = (model("Schedule")->getScheduleCount() < 100) ? ( model("Schedule")->getScheduleCount() + 0 ) : model("Schedule")->getScheduleCount();
        return inputOut("", "", $result);
    }

    /**
     * 获取矿机列表
     * @return array
     */
    function getMillList(){
        $id = input("id");
        $limit =false;
        $where = 'l.User_Num = "'.$this->userNum.'"';
        if ($id > 0)
            $where .= "and l.Id = ".$id;
        else
            $limit =[input("page"), input("limit")];
        $result = model("MiningList")->getList($where,$limit);
        return inputOut("","",$result);
    }

    /**
     * 选择矿机
     * @return array
     */
    function chooseMill(){
        try{
            $data = input("post.");
            MyLog("选择矿机请求", $data);
            $Miner = new MinerController("index");
            Db::startTrans();
            $msg = $Miner->chooseMill($this->userNum, $data['id'], $data['type']);
            if ($msg) exception($msg,1111);
            Db::commit();
            return inputOut();
        }catch (Exception $e){
            MyLog("选择矿机返回", $e->getMessage());
            Db::rollback();
            $msg = ($e->getCode() == 1111) ? $e->getMessage() :'代码异常';
            $code = ($e->getCode() == 1111) ? '0001' :'9999';
            return inputOut($msg,$code,$e->getMessage());
        }
    }

    /**
     * 获取下次挖矿时间
     * @param string $fieldName
     * @param bool $type
     * @return \think\response\Json
     */
    function getTime($type=false){
        $time = model("Schedule")->getScheduleList([
            'UserNum'=>$this->userNum,
            'prjectId'=>1
        ])[0];
        if ($type) return  $time;
        return json(strtotime($time['NextTime']) - time());
    }

    /**
     * 获取下级旷工列表
     * @return array
     */
    function getRebateList(){
        $result = model("Rebate")->getList("r.User_Num = '".$this->userNum."'");
        return inputOut("","",$result);
    }

    /**
     * 挖取宝石数量
     * @return \think\response\Json
     */
    function upNumber(){
        try{
            $user = $this->userNum;
            $id = input("id");
            $trueRecord = model("MillRecord")->getMillRecord([
                'User_Num'=> $user,
                'id'=> ['in',$id],
                'State'=> 1,
            ]);
            MyLog("收取宝石请求", $id);
            if (empty($trueRecord)) exception("无可用的记录", 1111);
            Db::startTrans();
            $data = ""; $truenumber = 0;$rebatenumber = 0;
            $userInfo = model("User")->getUserList(['User_Num'=>$user,'State'=>1])[0];
            //  添加返佣
            foreach ($trueRecord as $k => $v){
                $data .= $v['Id'].",";
                $rebate = model("Config")->getConfigList("Rebate")['Value'];
                $fre = ($v['Number'] * $rebate);
                //  添加返佣记录
                if ($userInfo['Acp_User_Num']){
                    if(model("Rebate")->addRebate(['User_Num'=>$userInfo['Acp_User_Num'], 'By_User_Num'=>$user, 'Numbler' => $fre, 'By_Id'=>$v['Id']]) <= 0)
                        exception("修改返佣记录失败", 1111);
                }
                $truenumber += $v['Number'];
                $rebatenumber += $fre;
            }

            //  修改记录
            if (model("MillRecord")->updata_Mill(['Id'=> ['in',trim($data,",")]], ['State'=>0, 'Start_Time'=>date("Y-m-d H:i:s")]) < 0)
                exception("修改数据失败", 1111);

            //  修改挖矿进度
            $Schedule = new ScheduleController("index");
            $result = $Schedule->upSchedule($user,count($trueRecord));
            if ($result) exception($result, 1111);

            //  修改用户宝石
            $Stone = $userInfo['Stone'] + $truenumber;

            //  用户添加日志
            model("UserLog")->addUserLog([
                'User_Num'              =>  $userInfo['User_Num'],
                'Log'                   =>   "挖矿: 记录Id: " .$id ,
                'Type'                  =>  3 ,
                'Stone'                 =>  $userInfo['Stone'],
                'Frozen_Stone'          =>  $userInfo['Frozen_Stone'],
                'Change_Stone'          =>  $truenumber,
            ]);

            if(model("User")->upUser(['User_Num'=>$user, 'State'=>1], ['Stone'=> $Stone]) < 0)
                exception("修改用户宝石失败", 1111);

            //  添加返佣日志
            if (($userInfo['Acp_User_Num'])){
                $AcpuserInfo = model("User")->getUserList(['User_Num'=>$userInfo['Acp_User_Num'],'State'=>1])[0];
                model("UserLog")->addUserLog([
                    'User_Num'              =>  $AcpuserInfo['User_Num'],
                    'Log'                   =>   "返佣，来源：" .$userInfo['User_Num'] ,
                    'Type'                  =>  3 ,
                    'Stone'                 =>  $AcpuserInfo['Stone'],
                    'Frozen_Stone'          =>  $AcpuserInfo['Frozen_Stone'],
                    'Change_Stone'          =>  $rebatenumber,
                ]);
            }

            Db::commit();
            return inputOut();
        }catch (Exception $e){
            Db::rollback();
            $msg = ($e->getCode() == 1111) ? $e->getMessage() :'代码异常';
            $code = ($e->getCode() == 1111) ? '0001' :'9999';
            if ($code == '9999')
                MyLog("收取宝石返回：", $e->getMessage()."-//////-".$e->getTraceAsString());
            else
                MyLog("收取宝石返回：", $e->getMessage());
            return inputOut($msg,$code,$e->getMessage());
        }
    }

    /**
     * 修改返佣数量
     * @return \think\response\Json
     */
    function getRebateNumber(){
        try{
            $user = $this->userNum;
            $By_User_Num = input("post.user");
            //  1 一键获取
            $type=input("post.type");
            if ($type == 1){
                $where=  "User_Num = '".$user."' and State = 0";
            }elseif ($type == 2){
                $where= "By_User_Num = '".$By_User_Num."' and State = 0";
            }
            MyLog("收取返佣请求", input("post."));

            Db::startTrans();
            $number = model("Rebate")->getNmber($where);
            if ($number > 0){
                if (model("Rebate")->UpNumber($where) <= 0)
                    exception("修改数量失败", 1111);
                //  修改用户宝石
                $info = model("User")->getUserList(['User_Num'=>$user,'State'=>1])[0];

                //  添加日志
                model("UserLog")->addUserLog([
                    'User_Num'              =>  $info['User_Num'],
                    'Log'                   =>   "领取返佣" ,
                    'Type'                  =>  3 ,
                    'Stone'                 =>  $info['Stone'],
                    'Frozen_Stone'          =>  $info['Frozen_Stone'],
                    'Change_Stone'          =>  $number,
                ]);

                if(model("User")->upUser(['User_Num'=>$user,'State'=>1], ['State'=>1, 'Stone'=> $info['Stone'] + $number]) <= 0)
                    exception("修改用户宝石失败", 1111);
                Db::commit();
            }
            return inputOut();
        }catch (Exception $e){
            Db::rollback();
            $msg = ($e->getCode() == 1111) ? $e->getMessage() :'代码异常';
            $code = ($e->getCode() == 1111) ? '0001' :'9999';
            if ($code == '9999')
                MyLog("收取返佣返回：", $e->getMessage()."-//////-".$e->getTraceAsString());
            else
                MyLog("收取返佣返回：", $e->getMessage());
            return inputOut($msg,$code,$e->getMessage());
        }
    }
}