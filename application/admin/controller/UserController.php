<?php
/**
 * Created by PhpStorm.
 * User: Teluns
 * Date: 2018/7/30
 * Time: 17:34
 */
namespace app\admin\controller;

class UserController extends Common
{

    /**
     * 获取用户列表
     * @return \think\response\Json
     */
    function  getUserList()
    {
        $page = input("page")?input("page"):1;
        $limit = input("limit")?input("limit"):10;
        $limitparam = $limit ? [$page, $limit] : false;
        $sizeData = model("User")->countUser();
        $maxpage = ceil($sizeData/$limit);
        return inputOut("","",model("User")->getUserList(false, ", Id, State", $limitparam),"",$maxpage,[$sizeData,0]);
    }


}
