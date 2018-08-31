<?php

namespace app\admin\controller;
use think\Controller;
/**
 * 后台
 * Class Index
 * @package app\admin\controller
 */
class IndexController extends Controller
{
    /**
     * 登陆
     * @return mixed
     */
    public function login()
    {
        if (cache(session_id()."_admin")) return redirect("admin/index/index");
        $data = input("post.");
        if (!empty($data)) {
            if (!captcha_check($data['code'], "admin_login")) return inputOut("图片验证码错误",'0001');
            $result = model("Admin")->getAdminList("name='".$data["account"]."' or mobile='".$data["account"]."'");
            if (!$result) return inputOut("没有该操作员",'0001');
            if ($result[0]['pass'] != md5($data['password']."mining_admin")) return inputOut("密码有误",'0001');
            model("SysLog")->addLog($result[0]['mobile'], "登陆后台");
            cache(session_id()."_admin", $result[0]['mobile'], 3600);
            cache($result[0]['mobile']."_mining_admin", $result[0], 3600);
            return inputOut();
        }
        return view();
    }

    /** 退出登陆
     * @return \think\response\Redirect
     */
    function logout()
    {
        cache(cache(session_id()."_admin") . "_mining_admin", null);
        cache(session_id()."_admin",null);
        return redirect("admin/index/login");
    }

    /**
     * 主页
     * @return \think\response\View
     */
    public function index(){
        if (!cache(session_id()."_admin"))
            return redirect("admin/index/login");
        return view();
    }

    /** 查询登陆状态
     * @return array
     */
    function Islogin(){
        $mobile = cache(session_id()."_admin");
        if (!$mobile) return inputOut("登陆失效", '1001');
        return inputOut("", "", cache($mobile."_mining_admin"));
    }
}