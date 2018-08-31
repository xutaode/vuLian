<?php
/**
 * Created by PhpStorm.
 * User: 徐涛
 * Date: 2018/7/25
 * Time: 22:30
 */

namespace app\common\model;
use think\Model;

/**
 * 操作员
 * Class Admin
 * @package app\common\model
 */
class Admin extends Model
{
    /**
     * @var string
     */
    protected $field = "id, name, mobile, role, add_time, pass";

    /**
     * 获取操作员信息
     * @param bool $where
     * @param bool $field
     * @return bool
     */
    function getAdminList($where=false, $field=false){
        $this->field .= $field;
        $result = $this::where($where)->field($this->field)->select();
        if ($result->isEmpty())
            return false;
        return $result->toArray();
    }

    /** 修改操作员信息
     * @param $where
     * @param $data
     * @return $this
     */
    function updateConfig($where, $data){
        return $this::where($where)->update($data);
    }


}