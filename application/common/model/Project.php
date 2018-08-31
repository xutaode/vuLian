<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/7/28
 * Time: 17:17
 */

namespace app\common\model;
use think\Model;

/**
 * 矿场
 * Class project
 * @package app\common\model
 */
class Project extends Model
{
    /**
     * @var string
     */
    protected  $field = "*";
    /**
     * 获取矿场列表
     * @param $field
     * @param $where
     * @return array|bool
     */
    function getProjectList($where=false, $field=false){
        $this->field .= $field;
        $result = $this::where($where)->field($this->field)->select();
        if ($result->isEmpty())
            return false;
        return $result->toArray();
    }

    /** 修改矿场信息
     * @param $where
     * @param $data
     * @return $this
     */
    function updateProject($where, $data){
        return $this::where($where)->update($data);
    }
}