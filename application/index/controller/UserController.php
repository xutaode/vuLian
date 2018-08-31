<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/7/27
 * Time: 13:35
 */

namespace app\index\controller;
use app\common\service\ShortMessage;
use think\Exception;

/**
 * Class User
 * @package app\index\controller
 */
class UserController extends baseController
{
    /**
     * 获取登陆状态
     * @return false|\PDOStatement|string|\think\Collection
     */
    function Islogin(){
        $userNum = cache(session_id()."_index");
        if (!$userNum) return inputOut("登陆失效","1001");
        return inputOut("","",model("User")->getUserList(['User_Num'=>$userNum,'State'=>1])[0]);
    }

    /**
     * 获取用户列表
     * @return array
     */
    function getUserList(){
        $userNum = cache(session_id()."_index");
        return inputOut("","",model("User")->getUserList(['User_Num'=>$userNum,'State'=>1]));
    }
	
	/**
     * 获取用户列表
     * @return array
     */
    function getUserInfo(){
        $post = input("post.");
        return inputOut("","",model("User")->getUserList(['User_Num'=>$post['user'],'State'=>1])[0]);
    }

    /**
     * 获取资产记录
     * @return array
     */
    function getMillRecord(){
        $page = input("page");
        $limit = input("limit");
        $limitparam = $limit ? [$page, $limit] : false;
        $sizeData = model("MillRecord")->getMillCount(['User_Num'=>$this->userNum,'State'=>0]);
        $maxpage = ceil($sizeData/$limit);
        $result = model("MillRecord")->getMillRecord(['User_Num'=>$this->userNum,'State'=>0],false,$limitparam);
        return inputOut("","",$result,"", $maxpage, $sizeData);
    }

    /**
     * 修改密码
     * @return array
     */
    function upPassWrod(){
        try{
            $data = input("post.");
            MyLog("修改密码请求", $data);
            if (!cache($data['mobile']."pic"))
                exception("请先发送短信",1111);

            //  短信验证码
            $shorMsg = new ShortMessage();
            $errorMsg = $shorMsg->chickMsg($data['mobile'], $data['mobileCode']);
            if ($errorMsg)
                exception($errorMsg,1111);
            cache($data['mobile']."pic", null);

            if (model("User")->upUser(['Mobile'=>$data['mobile'],'State'=>1], ['Pass_Word' => md5($data['password']."mining_index")]) <= 0)
                exception("操作失败", 1111);
            //  添加日志
            model("UserLog")->addUserLog([
                'User_Num'              =>  $this->userNum,
                'Log'                   =>   "修改密码",
            ]);
            return inputOut();
        }catch (Exception $e){
            $msg = ($e->getCode() == 1111) ? $e->getMessage() :'代码异常';
            $code = ($e->getCode() == 1111) ? '0001' :'9999';
            if ($code == '9999')
                MyLog("修改密码返回：", $e->getMessage()."-//////-".$e->getTraceAsString());
            else
                MyLog("修改密码返回：", $e->getMessage());
            return inputOut($msg,$code,$e->getMessage());
        }
    }

    /**
     * 修改昵称
     * @return array
     */
    function UpdatenickName(){
        try{
            MyLog("修改昵称请求", input("post."));

            if (input("name") == "" || !input("name"))
                exception("昵称不能为空", 1111);

            if (model("User")->upUser(['User_Num'=>$this->userNum, 'State'=>1], ['User_Name'=>input("name")]) < 0)
                exception("修改昵称失败", 1111);

            //  添加日志
            model("UserLog")->addUserLog([
                'User_Num'              =>  $this->userNum,
                'Log'                   =>   "修改昵称：".input("name"),
            ]);

            return inputOut();
        }catch (Exception $e){
            $msg = ($e->getCode() == 1111) ? $e->getMessage() :'代码异常';
            $code = ($e->getCode() == 1111) ? '0001' :'9999';
            if ($code == '9999')
                MyLog("修改昵称返回：", $e->getMessage()."-//////-".$e->getTraceAsString());
            else
                MyLog("修改昵称返回：", $e->getMessage());
            return inputOut($msg,$code,$e->getMessage());
        }
    }

    /**
     * 获取会员等级列表
     * @return array
     */
    function getUserLevel(){
        return inputOut("","",model("UserLevel")->getUserLevel());
    }
}