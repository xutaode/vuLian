<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/7/30
 * Time: 10:44
 */

namespace app\common\model;
use think\Model;

/**
 * 会员等级
 * Class UserLevel
 * @package app\common\model
 */
class UserLevel extends Model
{
    /**
     * @var string
     */
    protected $field = "Id, Level_Name, Hashrate, Balance, Mill_Time, State, Add_Time";

    /**
     * 获取会员等级
     * @param bool $where
     * @param bool $field
     * @return array|bool
     */
    function getUserLevel($where=false){
        $list = cache("userLevel");
        if (!$list){
            $list = FromFieldGetArray($this::select()->toArray(), 'Id');
            cache("userLevel", $list, 86400);
        }
        if ($where) return $list[$where];
        return $list;
    }

    /**
     * 修改会员等级
     * @param $data
     * @param bool $where
     * @return $this
     */
    function upUser($data, $where=false){
        return $this::where($where)->update($data);
    }
}