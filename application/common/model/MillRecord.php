<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/7/27
 * Time: 12:04
 */

namespace app\common\model;
use think\Model;

/**
 * 挖矿记录
 * Class MillRecord
 * @package app\common\model
 */
class MillRecord extends Model
{
    /**
     * @var string
     */
    protected $where = "";
    /**
     * @var string
     */
    protected $field = "Id, User_Num, Number, State, Add_Time, Start_Time";

    /**
     * 获取挖矿列表
     * @param $user
     * @param $where
     * @param $limit
     * @return false|\PDOStatement|string|\think\Collection
     */
    function getMillRecord($where=false,$field=false,$limit=false){
        $this->field .= $field;
        $limitNumber = $limit ? $limit[1] > 50 ? 50: $limit[1]: false;
        $limit1 = $limit ? $limitNumber * ($limit[0] - 1): false;
        $limit2 = $limit ? $limit[0] * $limitNumber: false;
        $result = $this::where($where)->field($this->field)->limit($limit1, $limit2)->order("Start_Time desc")->select();
        if ($result->isEmpty())
            return false;
        return $result->toArray();
    }

    /**
     * 在线挖矿人数
     * @return float|int
     */
    function getMillCount($where = ['state'=>1]){
        $result = $this::where($where)->count("Id");
        return $result;
    }

    /**
     * 修改记录
     * @param $where
     * @param $data
     * @return $this
     */
    function updata_Mill($where, $data){
        return $this::where($where)->update($data);
    }
}