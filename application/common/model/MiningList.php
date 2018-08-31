<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/7/28
 * Time: 17:25
 */

namespace app\common\model;
use think\Model;

/**
 * 矿机列表
 * Class miningList
 * @package app\common\model
 */
class MiningList extends Model
{
    /**
     * @var string
     */
    protected  $field = "Id, Mining_Id, User_Num, State, Add_Time, Source_Num, Hashrate, Leval_Name, Time, Mill_Number";

    /**
     * 获取矿机等级列表
     * @param bool $where
     * @param bool $field
     * @return array|bool
     */
    function getMiningList($where=false, $field=false){
        $this->field .= $field;
        $result = $this::where($where)->field($this->field)->select();
        if ($result->isEmpty())
            return false;
        return $result->toArray();
    }

    /**
     * 当前用户详情
     * @param bool $where
     * @return array|bool
     */
    function getList($where=false,$limit=false){
        $limitNumber = $limit ? $limit[1] > 50 ? 50: $limit[1]: false;
        $limit1 = $limit ? $limitNumber * ($limit[0] - 1): false;
        $limit2 = $limit ? $limit[0] * $limitNumber: false;
        $result = $this::alias("l")
            ->join("xz_mining m",'l.Mining_Id = m.Id','LEFT')
            ->field("l.*, m.Mining_Name, m.Hashrate, m.Level, m.Id levelId")
            ->where($where)
            ->limit($limit1, $limit2)
            ->select();
        if ($result->isEmpty()) return false;
        return $result->toArray();
    }

    /**
     * 添加矿机
     * @param $data
     * @return int|string
     */
    function addMiningList($data){
        $insert = array(
            'Add_Time'          =>  date("Y-m-d H:i:s"),
        );
        $result = array_merge($data,$insert);
        return $this::insert($result);
    }

    /** 修改矿机信息
     * @param $where
     * @param $data
     * @return $this
     */
    function updateMiningList($where, $data){
        return $this::where($where)->update($data);
    }
}