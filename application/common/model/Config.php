<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/7/28
 * Time: 17:02
 */

namespace app\common\model;
use think\Model;

class Config extends Model
{
    /**
     * @var string
     */
    protected $field="*";
    /**
     * 获取配置列表
     * @param bool $where
     * @param bool $field
     * @return array|bool
     */
    function getConfigList($code=false){
        $list = false;// cache("ConfigList");
        if (!$list){
            $list = FromFieldGetArray($this::select()->toArray(), 'Code');
            cache("ConfigList", $list);
        }
        if ($code)
            return $list[$code];
        return $list;
    }

    /**
     * 添加配置
     * @param $data
     * @return int|string
     */
    function addConfig($data){
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
    function updateConfig($where, $data){
        return $this::where($where)->update($data);
    }
}