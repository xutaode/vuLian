<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/7/27
 * Time: 10:22
 */

namespace app\common\model;
use think\Model;

/** 挖矿进度
 * Class Schedule
 * @package common\model
 */
class Schedule extends Model
{
    /**
     * @var string
     */
    protected $where = "";

    /**
     * @var string
     */
    protected $field = "Id, UserNum, Amount, TotalMiningAmount, AddTime, State, NextTime, prjectId, prjectId, Mining_Minute, Hashrate, Acp_UserNum";

    /**
     * 获取挖矿进度列表
     * @param bool $where
     * @param bool $filed
     * @return array|bool
     */
    function getScheduleList($where = false, $filed = false){
        $this->field .= $filed;
        $result = $this::where($where)->field($this->field)->select();
        if ($result->isEmpty())
            return false;
        return $result->toArray();
    }

    /**
     * 在线挖矿人数
     * @return float|int
     */
    function getScheduleCount($where = ['State'=>1]){
        $result = $this::where($where)->count("Id");
        return $result;
    }

    /**
     * 修改挖矿进度
     * @param $where
     * @param $data
     * @return $this
     */
    function upSchedule($where, $data){
        return $this::where($where)->update($data);
    }

    /**
     * 添加进度
     * @param $data
     * @return int|string
     */
    function addSchedule($data){
        $time = date("Y-m-d H:i:s");
        $insert = [
            'AddTime' => $time,
            'prjectId'=>1
        ];
        $result = array_merge($data, $insert);
        return $this::insert($result);
    }

    /**
     * sql 修改
     * @param $set
     * @param $where
     * @return int
     */
    function upScheduleSql($set, $where){
        $sql = "update xz_schedule set ".$set." where ".$where;
        return $this::execute($sql);
    }

}