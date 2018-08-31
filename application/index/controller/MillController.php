<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/7/30
 * Time: 14:50
 */

namespace app\index\controller;
use app\admin\controller\MinerController;
use think\Db;
use think\Exception;

/**
 * 矿机交易
 * Class MillController
 * @package app\index\controller
 */
class MillController extends baseController
{
    /**
     * 获取矿机交易列表
     * @return array
     */
    function getMillTreadList(){
        $user = $this->userNum;
        //  状态 1 交易中   2 完成   3 失效
        $state = input("state") ? input("state"): 1;

        //  购买出售状态（单个用户）  1 购买   2 出售   9 完成   3  4(全部)
        $milltype = input("milltype") ? input("milltype"): false;

        //  分页
        $page = input("page") ? input("page"): 1;
        $limit = input("limit") ? input("limit"): 10;

        //  取单条信息   type = 1
        $id = input("id") ? input("id"): false;
        $type = input("type") ? input("type"): false;
        $limitparam = $limit ? [$page, $limit] : false;

        if (!$milltype) {
            $where = "t.state=" . $state;
            $sizeDataWhere = ['State' => $state];
        }
        else{
            $stateWhere = false;
            if ($state == 1) $stateWhere = " and t.state=".$state;

            if ($milltype == 1){
                $where="t.BuyNum = '".$user."' and t.type = ".$milltype.$stateWhere;
                $sizeDataWhere = ['BuyNum' => $user, 'type' => 1];
            }elseif($milltype == 2){
                $where="t.SellNum = '".$user."' and t.type = ".$milltype.$stateWhere;
                $sizeDataWhere = ['SellNum' => $user, 'type' => 2];
            }elseif ($milltype == 9){
                $where= "(t.SellNum = '".$user."' or t.BuyNum = '".$user."') and t.state = 2";
                $sizeDataWhere = "(SellNum = '".$user."' or BuyNum = '".$user."') and state = 2";
            }else{
                $sizeDataWhere = ['State' => $state, 'type'=> ($milltype-2)];
                $where = "t.type = ".($milltype-2).$stateWhere;
            }
        }
        $sizeData = model("MillTread")->countMillTread($sizeDataWhere);
        $maxpage = ceil($sizeData/$limit);
        if ($type == 1) $where .= " and t.Id = ".$id;
        return inputOut("", $user,model("MillTread")->getList($where, $limitparam),"",$maxpage, $sizeData);
    }

    /**
     * 获取等级
     * @return array
     */
    function getMillLevel(){
        $result = FromFieldGetArray(model("Mining")->getMining(),['Hashrate','Mill_Time','State','Project_Id','Add_Time','Up_Time','Level'],'delet');
        return inputOut("","", $result);
    }

    /**
     * 推送消息
     * @return \think\response\Json
     */
    function NoteMsg(){
        $user = $this->userNum;
        $type = input("type") ? input("type") : 1;  //  1 查询   2 修改
        if ($type == 1)
            $result = model("MillTread")->getMillTreadList("(SellNum = '".$user."' or BuyNum = '".$user."') and ShowState = 2");
        else
            $result = model("MillTread")->updateMillTread("(SellNum = '".$user."' or BuyNum = '".$user."') and ShowState = 2", ['ShowState'=>1]);
        if (!$result) return json(1);
        return json(2);
    }

    /**
     * 发布矿机交易
     * @return array
     */
    function SendMill(){
        try{
            $data = input("post.");
            MyLog("发布交易请求：", $data);
            $insert1 = [
                'Price' => $data['Price'],
            ];

            Db::startTrans();
            if ($data['type'] == 1){
                $insert2 = [
                    'Mill_Level_Id' => $data['levelId'],
                    'Type' => '1',
                    'BuyNum' => $this->userNum,
                ];

                $info = model("User")->getUserList(['User_Num'=>$this->userNum,'State'=>1])[0];
                if($info['Stone'] < $insert1['Price']) exception("你的可用宝石不够",1111);

                //  冻结宝石失败
                if (db("user")->execute("update xz_user set Stone = Stone - ".$insert1['Price'].", Frozen_Stone = Frozen_Stone + ".$insert1['Price']." where User_Num = '".$this->userNum."'") < 0)
                    exception("冻结宝石失败",1111);

                //  添加日志
                model("UserLog")->addUserLog([
                    'User_Num'                              =>  $info['User_Num'],
                    'Log'                                    =>   "发布购买",
                    'Type'                                   =>  2,
                    'Stone'                                  =>  $info['Stone'],
                    'Frozen_Stone'                          =>  $info['Frozen_Stone'],
                    'Change_Stone'                          =>  '-'.$insert1['Price'],
                    'Change_Frozen_Stone'                 =>  '+'.$insert1['Price'],
                ]);
            }else{
                $result = $this->getMyMill($data['id'])->getdata()['data'][0];
                if (!$result) exception("没有此矿机数据",1111);
                //  修改矿机状态
                model('MiningList')->updateMiningList(['Id'=>$result['Id']], ['State'=>3]);
                if ($result['State'] == 0){
                    $Miner = new MinerController("index");
                    $Miner->chooseMill($this->userNum, $result['Id'], 'stop');
                }
                $insert2 = [
                    'Type' => '2',
                    'SellNum' => $this->userNum,
                    'Mill_Id' => $result['Id'],
                    'Mill_Level_Id' => $result['levelId'],
                ];
                //  添加日志
                model("UserLog")->addUserLog([
                    'User_Num'                              =>  $this->userNum,
                    'Log'                                    =>   "发布出售". $insert1['Price'] . ", 矿机编号：".$result['Mill_Number'],
                    'Type'                                   =>  2,
                ]);
            }
            if (model('MillTread')->addMillTread(array_merge($insert1,$insert2)) <= 0) exception("添加失败",1111);

            Db::commit();
            return inputOut();
        }catch (Exception $e){
            Db::rollback();
            $msg = ($e->getCode() == 1111) ? $e->getMessage() :'代码异常';
            $code = ($e->getCode() == 1111) ? '0001' :'9999';
            if ($code == '9999')
                MyLog("发布交易返回：", $e->getMessage()."-//////-".$e->getTraceAsString());
            else
                MyLog("发布交易返回：", $e->getMessage());
            return inputOut($msg,$code,$e->getMessage());
        }
    }

    /**
     * 取消发布矿机
     * @return array
     */
    function cancelSendMill(){
        try{
            $data = input("post.");
            MyLog("取消发布：", $data);

            Db::startTrans();
            $info = model("MillTread")->getMillTreadList(['Id'=>$data['id'], 'State'=>1]);
            if (!$info) exception("没有此交易信息", 1111);
            $info = $info[0];
            if ($info['BuyNum'] != $this->userNum && $info['SellNum'] != $this->userNum) exception("你不能取消别人的交易", 1111);
            //  购买
            if ($info['Type'] == 1){
                $Userinfo = model("User")->getUserList(['User_Num'=>$info['BuyNum'],'State'=>1])[0];
                //  修改冻结金额
                if (db("User")->execute("update xz_user set Stone = Stone + ".$info['Price'].", Frozen_Stone = Frozen_Stone - ".$info['Price']." where User_Num = '".$info['BuyNum']."' and Frozen_Stone >= ".$info['Price']) <= 0)
                    exception("修改冻结金额失败", 1111);
                //  添加日志
                model("UserLog")->addUserLog([
                    'User_Num'                              =>  $info['BuyNum'],
                    'Log'                                    =>   "取消购买",
                    'Type'                                   =>  2,
                    'Stone'                                  =>  $Userinfo['Stone'],
                    'Frozen_Stone'                          =>  $Userinfo['Frozen_Stone'],
                    'Change_Stone'                          =>  '+'.$info['Price'],
                    'Change_Frozen_Stone'                 =>  '-'.$info['Price'],
                ]);
            }
            //  出售
            elseif($info['Type'] == 2){
                //  修改矿机状态
                if (model('MiningList')->updateMiningList(['Id'=>$info['Mill_Id'], 'User_Num'=>$info['SellNum']], ['State'=>1]) < 0)
                    exception("修改矿机失败", 1111);

                //  添加日志
                model("UserLog")->addUserLog([
                    'User_Num'                              =>  $info['SellNum'],
                    'Log'                                    =>   "取消出售，修改矿机状态.矿机id:".$info['Mill_Id'],
                    'Type'                                   =>  2
                ]);
            }
            //  修改交易数据
            if (model("MillTread")->updateMillTread(['Id'=>$info['Id']] , ['State'=>3]) < 0)
                exception("修改交易数据失败", 1111);

            Db::commit();
            return inputOut();
        }catch (Exception $e){
            Db::rollback();
            $msg = ($e->getCode() == 1111) ? $e->getMessage() :'代码异常';
            $code = ($e->getCode() == 1111) ? '0001' :'9999';
            if ($code == '9999')
                MyLog("取消发布返回：", $e->getMessage()."-//////-".$e->getTraceAsString());
            else
                MyLog("取消发布返回：", $e->getMessage());
            return inputOut($msg,$code,$e->getMessage());
        }
    }

    /**
     * 矿机交易
     * @return array
     */
    function buySellMill(){
        try{
            $data = input("post.");
            MyLog("矿机交易请求：", $data);

            $id= $data['id'];
            $result = model("MillTread")->getMillTreadList(['Id'=>$id,'State'=>1]);
            if (!$result) exception("请重新选择交易矿机",1111);
            $result = $result[0];
            // 1 购买
            if ($data['type'] == 1){
                $buyNum = $this->userNum;
                $millId = $result['Mill_Id'];
            }elseif($data['type'] == 2){
                $buyNum = $result['BuyNum'];
                $millId = $data['sellmillId'];
            }
            $sellmill = model("MiningList")->getMiningList(['Id'=>$millId]);
            if (!$sellmill) exception("请重新选择卖出矿机",1111);
            $sellmill = $sellmill[0];
            if ($sellmill['Mining_Id'] != $result['Mill_Level_Id']) exception("选择矿机不符合出售等级要求",1111);
            if ($buyNum == $sellmill['User_Num']) exception("自己不能与自己交易",1111);

            Db::startTrans();
            //  减少冻结宝石数量
            $buyUser = model("User")->getUserList(['User_Num'=>$buyNum])[0];
            if ($data['type'] == 2){
                if (db("user")->execute("update xz_user set Frozen_Stone = Frozen_Stone - ".$result['Price']." where User_Num = '".$buyNum."'") < 0)
                    exception("修改卖家宝石失败",1111);
            }else{
                $buyBlance = $buyUser['Stone'];
                if ($buyBlance < $result['Price']) exception("你的宝石数量不足",1111);

                if (model("User")->upUser(['User_Num' => $buyNum], ['Stone' => $buyBlance - $result['Price']]) <= 0)
                    exception("修改卖家宝石失败",1111);
            }

            //  添加宝石
            $sellUser = model("User")->getUserList(['User_Num'=>$sellmill['User_Num']])[0];
            if (model("User")->upUser(['User_Num' => $sellmill['User_Num']], ['Stone' => $sellUser['Stone'] + $result['Price'],]) <= 0)
                exception("增加买家宝石失败",1111);

            //  修改买家矿机
            if(model("MiningList")->updateMiningList(['Id'=>$sellmill['Id']], ['User_Num' => $buyUser['User_Num'], 'State'=>1]) <= 0)
                exception("修改矿机失败",1111);

            //  修改交易记录
            if(model("MillTread")->updateMillTread(['Id'=>$id], ['State'=>2, 'SellNum'=>$sellmill['User_Num'], 'BuyNum'=>$buyNum,'Mill_Id'=>$sellmill['Id'], 'ShowState'=>2, 'EndTime'=>date("Y-m-d H:i:s")]) <= 0)
                exception("修改记录失败",1111);

            //  添加卖家日志
            model("UserLog")->addUserLog([
                'User_Num'              =>  $buyUser['User_Num'],
                'Log'                   =>   "购买矿机，编号：".$sellmill['Mill_Number'],
                'Type'                  =>  2,
                'Stone'                 =>  $buyUser['Stone'],
                'Frozen_Stone'          =>  $buyUser['Frozen_Stone'],
                'Change_Stone'          =>  ($data['type'] == 2) ? 0: '-'.$result['Price'],
                'Change_Frozen_Stone'  =>  ($data['type'] == 2) ? '-'.$result['Price']: 0,
            ]);
            //  添加买家日志
            model("UserLog")->addUserLog([
                'User_Num'              =>  $sellUser['User_Num'],
                'Log'                   =>   "卖出矿机，编号".$sellmill['Mill_Number'],
                'Type'                  =>  2,
                'Stone'                 =>  $sellUser['Stone'],
                'Frozen_Stone'          =>  $sellUser['Frozen_Stone'],
                'Change_Stone'          =>   '+'.$result['Price'],
                'Change_Frozen_Stone'  =>  0,
            ]);

            Db::commit();
            return inputOut();
        }catch (Exception $e){
            Db::rollback();
            $msg = ($e->getCode() == 1111) ? $e->getMessage() :'代码异常';
            $code = ($e->getCode() == 1111) ? '0001' :'9999';
            if ($code == '9999')
                MyLog("矿机交易返回：", $e->getMessage()."-//////-".$e->getTraceAsString());
            else
                MyLog("矿机交易返回：", $e->getMessage());
            return inputOut($msg,$code,$e->getMessage());
        }
    }

    /**
     * 获取当前用户矿机
     * @return array
     */
    function getMyMill($id=false){
        $where = "l.State = 1 and l.User_Num = '".$this->userNum."'";
        if ($id) $where = "l.Id = ".$id." and ".$where;
        return inputOut("","", model("MiningList")->getList($where));
    }
}