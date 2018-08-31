<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/8/1
 * Time: 18:25
 */

namespace app\common\model;
use think\Model;

/**
 * 提现
 * Class WithdrawController
 * @package app\common\model
 */
class Withdraw extends Model
{
    /**
     * @var string
     */
    protected $field = "Id, Order_Num, User_Num, Address, Number, Add_Time, State, Sbreak";

    /**
     * 获取提现列表
     * @param bool $where
     * @param bool $field
     * @return array|bool
     */
    function getWithdrawList($where=false, $field = false, $limit=false){
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
     * 统计记录
     * @return int|string
     */
    function countWithdrawList($where=false){
        return $this::where($where)->count("Id");
    }

    /**
     * 添加提现
     * @param $data
     */
    function addWithdraw($data){
        $time = date("Y-m-d H:i:s");
        $insert = [
            'Add_Time' => $time,
            'State'=>1
        ];
        $result = array_merge($data, $insert);
        return $this::insert($result);
    }

    /**
     * 修改提现信息
     * @param $data
     * @param $where
     * @return $this
     */
    function upWithdraw($where, $data){
        return $this::where($where)->update($data);
    }
}