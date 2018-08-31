<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/7/27
 * Time: 13:43
 */

namespace app\common\model;
use think\Model;

/**
 * Class User
 * @package app\common\model
 */
class User extends Model
{
    /**
     * @var string
     */
    protected $where = "";
    /**
     * @var stringgetUserList
     */
    protected $field = "User_Num, User_Name, Mobile, Stone, Hashrate, User_Level_Id, Acp_User_Num, CredentialNo,Real_Name, Add_Time, Frozen_Stone, address, Mining_Minute, InviteCode";

    /**
     * 获取用户信息
     * @param bool $where
     * @param bool $field
     * @param int $page
     * @param int $pageCount
     * @return array|bool
     */
    function getUserList($where=false, $field = false, $limit=false){
        $this->field .= $field;
        $limitNumber = $limit ? $limit[1] > 50 ? 50: $limit[1]: false;
        $limit1 = $limit ? $limitNumber * ($limit[0] - 1): false;
        $limit2 = $limit ? $limit[0] * $limitNumber: false;

        $result = $this::where($where)->field($this->field)->limit($limit1,$limit2)->select();
        if ($result->isEmpty())
            return false;
        return $result->toArray();
    }

    /**
     * 添加用户
     * @param $data
     * @return int|string
     */
    function addUser($data){
        $time = date("Y-m-d H:i:s");
        $insert = [
            'Add_Time' => $time,
            'Last_Ip'=>request()->ip()
        ];
        $result = array_merge($data, $insert);
        return $this::insert($result);
    }

    /**
     * 统计用户
     * @param bool $where
     * @return int|string
     */
    function countUser($where=false){
        return $this::where($where)->count("Id");
    }

    /**
     * 修改用户信息
     * @param $data
     * @param $where
     * @return $this
     */
    function upUser($where, $data){
        return $this::where($where)->update($data);
    }
}