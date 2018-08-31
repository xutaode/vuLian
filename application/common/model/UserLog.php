<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/8/7
 * Time: 11:20
 */

namespace app\common\model;
use think\Model;

/**
 * 用户日志
 * Class UserLog
 * @package app\common\model
 */
class UserLog  extends Model
{
    /**
     * @var string
     */
    protected $field = "Id, User_Num, Log, Add_Time, Ip, Type, Stone, Frozen_Stone, Change_Stone, Change_Frozen_Stone";

    /**
     * 获取用户日志
     * @param bool $where
     * @param bool $field
     * @return bool
     */
    function getUserLogList($where=false, $limit){
        $limitNumber = $limit ? $limit[1] > 50 ? 50: $limit[1]: false;
        $limit1 = $limit ? $limitNumber * ($limit[0] - 1): false;
        $limit2 = $limit ? $limit[0] * $limitNumber: false;
        $result = $this::where($where)->field($this->field)->limit($limit1, $limit2)->select();
        if ($result->isEmpty())
            return false;
        return $result->toArray();
    }

    /**
     * 统计用户日志
     * @param bool $where
     * @return int|string
     */
    function countUserLog($where=false){
        return $this::where($where)->count("Id");
    }

    /**
     * 添加用户日志
     * @param $data
     * @return int|string
     */
    function addUserLog($data){
        $insert = array(
            'Add_Time'          =>  date("Y-m-d H:i:s"),
            'Ip'                 =>  request()->ip(),
        );
        $result = array_merge($data,$insert);
        return $this::insert($result);
    }

    /** 修改用户日志
     * @param $where
     * @param $data
     * @return $this
     */
    function updateUserLog($where, $data){
        return $this::where($where)->update($data);
    }
}