<?php

namespace app\common\model;
use think\Exception;
use \think\Model;

/**
 * 角色
 * Class Role
 * @package app\common\model
 */
class Role extends Model
{
    /**
     * 获取角色列表
     * @param bool $where
     * @param bool $filed
     * @return array|bool
     */
    function getRoleList($where = false, $filed = false, $type=false){
        if ($type)
            $result = $this::where($where)->field($filed)->select();
        else
            $result = $this::alias("r")->join("xz_permiss_grup p","r.Code = p.Code",'LEFT')->where($where)->field($filed)->select();
        if ($result->isEmpty())
            return false;
        return $result->toArray();
    }

    /**
     * 添加角色
     * @param $data
     * @return int|string
     */
    function addRole($data){
        $insert = [
            'Add_Time'      =>  date("Y-m-d H:i:S"),
            'Desc'           =>  '添加角色',
            'Ip'             =>  request()->ip(),
        ];
        $result = array($data, $insert);
        return $this::insert($result);
    }

    /**
     * 修改角色信息
     * @param $where
     * @param $data
     * @return $this
     */
    function updateConfig($where, $data){
        return $this::where($where)->update($data);
    }

}