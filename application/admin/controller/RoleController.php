<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/7/26
 * Time: 13:56
 */

namespace app\admin\controller;
use think\Db;
use think\Exception;

/**
 * 角色 权限 权限组
 * Class Role
 * @package app\admin\controller
 */
class RoleController extends Common
{
    /**
     * 获取角色列表
     * @return \think\response\Json
     */
    function getList(){
        return inputOut("","", model("Role")->getRoleList(false,false,true));
    }

    /**
     * 获取角色组列表
     * @return \think\response\Json
     */
    function getRoleGrup(){
        return inputOut("","", model("PermissGrup")->getPermissGruplist());
    }

    /**
     * 添加角色
     * @return array
     */
    function addRole(){
        $data = input("post.");
        Db::startTrans();
        try{
            $result = model("Role")->addRole([
                'Name'           =>  $data['username'],
                'Code'           =>  $data['Code'],
                'state'          =>  $data['state'],
                'Add_Mobile'    =>  $this->mobile
            ]);
            if ($result <= 0) return inputOut("添加数据失败","0001","","",true);
            Db::commit();
            return inputOut();
        }catch (Exception $e){
            Db::rollback();
            return inputOut("代码异常","0001");
        }
    }

    /** 根据id获取权限列表
     * @param $id
     * @return array
     */
    function getRoleValue($id){
        $str = "";
        $role = explode("," ,model("Role")->getRoleList("r.state=1 and r.id=".$id, "p.Value")[0]['Value']);
        foreach ($role as $k => $v)  $str .= "'".$v."',";
        //  获取权限详情
        $data = model('Permissions')->getPermissionsInfo("id in (".trim($str,",").")");
        return GetListArray($data);
    }
}