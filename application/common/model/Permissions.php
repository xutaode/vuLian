<?php

namespace app\common\model;
use \think\Model;

class Permissions extends Model
{
    protected $field="Id ,Pid, Name text, Icon icon, Type, Url href, IsShow";

    /** 获取权限列表
     * @param bool $where
     * @param bool $field
     * @return bool|false|\PDOStatement|string|\think\Collection
     */
    function getPermissionsInfo($where = false, $field = false){
        $this->field .= $field;
        $result = $this::where($where)->field($this->field)->select();
        if ($result->isEmpty())
            return false;
        return $result->toArray();
    }

    /**
     *      检查权限
     * @param    string $nowrole    当前访问的页面
     * @param    string $roleList   权限列表
     * @return bool
     */
    public function  chicktPermissions($nowrole,$roleList)
    {
        $list = $this->getPermissionsList($roleList);
        if(!strpos(urldecode(http_build_query($list)),$nowrole))
        {
            echo "<script>alert('你的权限不足，请联系管理员！');window.location.href='".U("index/login")."';</script>";
            die();
        }
    }



}