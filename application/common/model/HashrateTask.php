<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/7/30
 * Time: 11:34
 */

namespace app\common\model;
use think\Model;

/**
 * 算力任务
 * Class HashrateTask
 * @package app\common\model
 */
class HashrateTask extends Model
{
    /**
     * @var string
     */
    protected $field="Name, Desc, TypeCode, AddTime, AddUser, EndTime, Hashrate, State";
    /**
     * 获取配置列表
     * @param bool $where
     * @param bool $field
     * @return array|bool
     */
    function getHashrateTaskList(){
        cache("HashrateTask",null);
        $list = cache("HashrateTask");
        if (!$list){
            $list = $this::select()->toArray();
            cache("HashrateTask", $list, 86400);
        }
        return $list;
    }

    /**
     * 添加配置
     * @param $data
     * @return int|string
     */
    function addHashrateTask($data){
        $time = date("Y-m-d H:i:s");
        $insert = array(
            'Add_Time'          =>  $time,
            'Up_Time'           =>  $time,
        );
        $result = array_merge($data,$insert);
        return $this::insert($result);
    }

    /** 修改配置
     * @param $where
     * @param $data
     * @return $this
     */
    function updateHashrateTask($where, $data){
        return $this::where($where)->update($data);
    }
}