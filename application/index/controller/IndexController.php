<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/7/30
 * Time: 10:37
 */

namespace app\index\controller;
use app\admin\controller\MinerController;
use app\common\service\regularVerify;
use app\common\service\ShortMessage;
use think\Controller;
use think\Db;
use think\Exception;

class IndexController extends Controller
{
    /**
     * 会员登陆
     * @return array|\think\response\Json
     */
    function login(){
        $userNum = input("userName");
        $pass = input("passWord");
        //  正则验证
        $validate = new regularVerify();
        if (!$validate->scene("login")->check([
            'tel'   =>  $userNum,
            'pwd'   =>  $pass
        ]))
            return inputOut($validate->getError(),"0001");
        $userInfo = model("User")->getUserList(['Mobile'=>$userNum], ",Pass_Word,Task,InviteCode,Mining_Minute");
        if (!$userInfo)
            return inputOut("没有此用户信息","0001");
        if ($userInfo[0]['Pass_Word'] != md5($pass."mining_index"))
            return inputOut("密码错误","0001");
        unset($userInfo[0]['Pass_Word']);
        cache(session_id()."_index", $userInfo[0]['User_Num'], 3600);
        cache($userInfo[0]['User_Num']."_mining_index", $userInfo[0], 3600);
        return inputOut();
    }

    /**
     * 注册
     * @return array
     */
    function register(){
        $data = input("get.");
        MyLog("注册请求数据", $data);
        try{
            //  正则验证
            $validate = new regularVerify();
            if (!$validate->scene("register")->check([
                'tel'                 =>  $data['mobile'],
                'pwd'                 =>  $data['passWrod'],
                'com_pwd'            =>  $data['repassword'],
                'Invitation_code'   =>  $data['InviteCode'],
                'v_code'              =>  $data['mobileCode'],
            ]))
                exception($validate->getError(),1111);
//            if (!cache($data['mobile']."pic")) exception("请先发送短信",1111);

            //  手机验证码
//            $shorMsg = new ShortMessage();
//            $errorMsg = $shorMsg->chickMsg($data['mobile'], $data['mobileCode']);if ($errorMsg) exception($errorMsg,1111);
//            cache($data['mobile']."pic", null);

            Db::startTrans();
            if (model("User")->getUserList(['Mobile'=>$data['mobile']])) exception("该手机号码已被注册",1111);

            $allConfig =  model("Config")->getConfigList();

            $config = [
                'Hashrate'  => $allConfig['Hashrate']['Value'],
                'Mining_Minute'  => $allConfig['Mining_Minute']['Value'],
            ];
            $insert = [
                'User_Num'  =>createdCode(),
                'User_Name'  =>$data['mobile'],
                'Mobile'  =>$data['mobile'],
                'Pass_Word'  =>md5($data['passWrod']."mining_index"),
                'InviteCode'  =>createdCode("Invite", $data['mobile']),
                'Acp_User_Num'  =>false,
                'Hashrate'  =>      $config['Hashrate'],
                'Mining_Minute'  =>$config['Mining_Minute'],
                'address'           => getAddress(),
            ];
            //  推荐码
            if ($data['InviteCode']){
                $Acp_User_Num = model("User")->getUserList(['InviteCode'=>$data['InviteCode']])[0];
                if (!$Acp_User_Num) exception("没有此推荐人信息",1111);
                $insert['Acp_User_Num'] = $Acp_User_Num['User_Num'];
            }
            if (model("User")->addUser($insert) < 0)
                exception("添加用户失败",1111);
            //  进度
            $scheInsert = [
                'UserNum'  =>$insert['User_Num'],
                'Acp_UserNum'=>$insert['Acp_User_Num'],
                'Hashrate'=>$insert['Hashrate'],
                'Mining_Minute'  =>$config['Mining_Minute'],
                'NextTime'      => date("Y-m-d H:i:s", strtotime("+1 minute"))
            ];
            if (model("Schedule")->addSchedule($scheInsert) < 0)
                exception("添加进度失败",1111);

            // 修改推荐人算力
            if ($insert['Acp_User_Num']){
                $Recommend = model("Config")->getConfigList("Recommend")['Value'];
                $miner = new MinerController("index");
                $tuijian = $miner->upHashrate($Acp_User_Num['User_Num'], $Recommend, 0,true);
                if ($tuijian) exception($tuijian,1111);
                //  添加日志
                model("UserLog")->addUserLog([
                    'User_Num'              =>  $insert['Acp_User_Num'],
                    'Log'                   =>   "推荐".$insert['User_Num'].", 获取算力：".$Recommend,
                ]);
            }

            //  添加日志
            model("UserLog")->addUserLog([
                'User_Num'              =>  $insert['User_Num'],
                'Log'                   =>   "新用户注册",
            ]);

            Db::commit();

            return inputOut();
        }catch (Exception $e){
            Db::rollback();
            $msg = ($e->getCode() == 1111) ? $e->getMessage() :'代码异常';
            $code = ($e->getCode() == 1111) ? '0001' :'9999';
            if ($code == '9999')
                MyLog("注册返回：", $e->getMessage()."-//////-".$e->getTraceAsString());
            else
                MyLog("注册返回：", $e->getMessage());
            return inputOut($msg,$code,$e->getMessage());
        }
    }

    /**
     * 发送验证码
     * @return array
     */
    function sendMsg(){
        $data = input("post.");

        //  正则验证
        $validate = new regularVerify();
        if (!$validate->scene("SendCode")->check([
            'tel'                 =>  $data['mobile'],
            'verify_code'        =>  $data['PicCode'],
        ]))
            return inputOut($validate->getError(),"0001");

        $id = isset($data['flg']) ? $data['flg'] : 'index_register';
        if (!in_array($id, ['index_register', 'index_upPass'])) return inputOut("参数错误","0001");
        //  图片验证码
        if(!captcha_check($data['PicCode'], $id)) return inputOut("图片验证码错误","0001");
        cache($data['mobile']."pic", 1);
        $shorMsg = new ShortMessage();
        $result = $shorMsg->sendMsg($data['mobile']);
        if ($result) return inputOut($result, "0001");
        return inputOut();
    }

    /**
     * 获取验证码
     * @return string
     */
    function gitCaptcha(){
        $id = input("id") ? input("id"): 'index_register';
        return captcha_src($id);
    }

    /**
     * 退出登录
     * @return \think\response\Json
     */
    function loginOut(){
        $userNum = cache(session_id()."_index");
        cache(session_id()."_index", null);
        cache($userNum."_mining_index", null);
        return inputOut();
    }
}