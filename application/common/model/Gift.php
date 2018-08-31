<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/8/2
 * Time: 14:29
 */

namespace app\common\model;
use think\Model;

/**
 * 礼包
 * Class Gift
 * @package app\common\model
 */
class Gift extends Model
{
    /**
     * @var string
     */
    protected $field = "Id, Name, Price, StartTime, LastTime, State, AddMobile, Addtime, Stone";

    /**
     * 获取礼包列表
     * @param bool $where
     * @param bool $field
     * @param bool $limit
     * @return array|bool
     */
    function getGift($where=false, $field = false, $limit=false){
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
     * 统计礼包记录
     * @return int|string
     */
    function countGift(){
        return $this::count("Id");
    }

    /**
     * 添加礼包
     * @param $data
     */
    function addGift($data){
        $time = date("Y-m-d H:i:s");
        $insert = [
            'Add_Time' => $time,
            'State'=>1
        ];
        $result = array_merge($data, $insert);
        return $this::insert($result);
    }

    /**
     * 修改礼包信息
     * @param $data
     * @param $where
     * @return $this
     */
    function upGift($where, $data){
        return $this::where($where)->update($data);
    }
}