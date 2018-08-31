<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/7/26
 * Time: 16:18
 */

namespace app\common\model;
use think\Model;

/** 权限组
 * Class PermissGrup
 * @package app\admin\model
 */
class PermissGrup extends Model
{
    /**
     * @var string
     */
    protected  $field = "*";

    /**
     * 获取权限组列表
     * @param bool $where
     * @param bool $field
     * @return array|bool
     */
    function getPermissGruplist($where=false, $field=false){
        $this->field .= $field;
        $result = $this::where($where)->field($this->field)->select();
        if ($result->isEmpty())
            return false;
        return $result->toArray();
    }

    /** 修改权限组信息
     * @param $where
     * @param $data
     * @return $this
     */
    function updateMining($where, $data){
        return $this::where($where)->update($data);
    }
}