<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/8/2
 * Time: 15:10
 */

namespace app\common\model;
use think\Model;

/**
 * 礼包记录
 * Class GiftRecord
 * @package app\common\model
 */
class GiftRecord extends Model
{
    /**
     * @var string
     */
    protected $field = "Id, GiftId, Order_Num, State, User_Num, Add_Time";

    /**
     * 获取礼包记录
     * @param bool $where
     * @param bool $field
     * @param bool $limit
     * @return array|bool
     */
    function getGiftRecord($where=false, $field = false, $limit=false){
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
    function countGiftRecord(){
        return $this::count("Id");
    }

    /**
     * 添加礼包记录
     * @param $data
     */
    function addGiftRecord($data){
        $insert = [
            'Order_Num' => createdCode('order'),
            'Add_Time' => date("Y-m-d H:i:s"),
            'State'=>1
        ];
        $result = array_merge($data, $insert);
        return $this::insert($result);
    }

    /**
     * 修改礼包记录
     * @param $data
     * @param $where
     * @return $this
     */
    function upGiftRecord($where, $data){
        return $this::where($where)->update($data);
    }
}