<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/7/30
 * Time: 14:48
 */

namespace app\common\model;
use think\Model;

/**
 * 矿机交易列表
 * Class MillTread
 * @package app\common\model
 */
class MillTread extends Model
{
    /**
     * @var string
     */
    protected $field = "Id, Mill_Level_Id, Type, SellNum, BuyNum, addTime, Price, Mill_Id, ShowState, EndTime";

    /**
     * 获取详情列表
     * @return array|bool
     */
    function getList($where=false, $limit){
        $limitNumber = $limit ? $limit[1] > 50 ? 50: $limit[1]: false;
        $limit1 = $limit ? $limitNumber * ($limit[0] - 1): false;
        $limit2 = $limit ? $limit[0] * $limitNumber: false;
        $result = $this::alias("t")
                ->join("xz_mining m",'t.Mill_Level_Id = m.Id','LEFT')
                ->join("xz_mining_list l",'t.Mill_Id = l.Id','LEFT')
                ->join("xz_user b",'b.User_Num = t.BuyNum','LEFT')
                ->join("xz_user se",'se.User_Num = t.SellNum','LEFT')
                ->field("t.Id, m.Mining_Name, m.Hashrate, m.Level, t.Price, t.type, t.SellNum, t.BuyNum, b.User_Name buy_Name, b.Mobile buy_Mobile, se.User_Name sell_Name, se.Mobile sell_Mobile, t.addTime Add_Time, t.State, l.Mill_Number")
                ->where($where)
                ->order("t.addTime desc")
                ->limit($limit1, $limit2)
                ->select();
        if ($result->isEmpty()) return false;
        return $result->toArray();
    }

    /**
     * 获取矿机交易信息
     * @param bool $where
     * @param bool $field
     * @return bool
     */
    function getMillTreadList($where=false, $field=false){
        $this->field .= $field;
        $result = $this::where($where)->field($this->field)->select();
        if ($result->isEmpty())
            return false;
        return $result->toArray();
    }

    /**
     * 统计
     * @param bool $where
     * @return int|string
     */
    function countMillTread($where=false){
        return $this::where($where)->count("Id");
    }

    /**
     * 添加矿机交易
     * @param $data
     * @return int|string
     */
    function addMillTread($data){
        $insert = array(
            'addTime'          =>  date("Y-m-d H:i:s"),
        );
        $result = array_merge($data,$insert);
        return $this::insert($result);
    }

    /** 修改矿机交易信息
     * @param $where
     * @param $data
     * @return $this
     */
    function updateMillTread($where, $data){
        return $this::where($where)->update($data);
    }
}