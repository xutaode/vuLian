<?php
/**
 * Created by PhpStorm.
 * User: 徐涛
 * Date: 2018/7/26
 * Time: 21:17
 */

namespace app\admin\controller;
/**
 * Class Admin
 * @package app\admin\controller
 */
class AdminController extends Common
{

    /**
     * 获取菜单列表
     * @return \think\response\Json
     */
    public function menu()
    {
        $user = cache($this->mobile."_mining_admin");
        //  获取权限组的值
        $role = new  RoleController();
        $result = $role->getRoleValue($user['role']);

        return inputOut("","",$result);
    }

    /**
     * 获取用户列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function getUserList(){
        return inputOut("",'',model('Admin')->getAdminList());
    }
}