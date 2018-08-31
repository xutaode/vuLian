<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/7/28
 * Time: 17:22
 */

namespace app\common\model;
use think\Model;

/**
 * 矿机等级
 * Class Mining
 * @package app\common\model
 */
class Mining extends Model
{
    /**
     * @var string
     */
    protected  $field = "*";

    /**
     * 获取矿机等级列表
     * @param bool $where
     * @param bool $field
     * @return array|bool
     */
    function getMining($where=false, $field=false){
        $this->field .= $field;
        $result = $this::where($where)->field($this->field)->select();
        if ($result->isEmpty())
            return false;
        return $result->toArray();
    }

    /** 修改矿机等级信息
     * @param $where
     * @param $data
     * @return $this
     */
    function updateMining($where, $data){
        return $this::where($where)->update($data);
    }
}