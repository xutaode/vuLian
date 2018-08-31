<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/7/27
 * Time: 17:15
 */

namespace app\common\model;
use think\Model;
use think\Db;

/** 返佣记录
 * Class Rebate
 * @package app\common\model
 */
class Rebate extends Model
{
    /**
     * @var string
     */
    protected  $field = "Id, User_Num, Numbler, True_Num, By_User_Num, Add_Time, State, Up_Time, By_Id";

    /** 添加记录
     * @param $data
     * @return int|string
     */
    function addRebate($data){
        $time = date("Y-m-d H:i:s");
        $insert = array(
            'Add_Time'  => $time,
            'State'     =>  0,
            'Up_Time'  => $time
            ,'True_Num'=>$data['Numbler']
        );
        $result = array_merge($data, $insert);
        return $this::insert($result);
    }

    /**
     * 获取下级矿工记录
     * @param $where
     * @return array
     */
    function getList($where){
        return $this::alias("r")->where($where)->join("xz_user u",'u.User_Num = r.User_Num','LEFT')->join("xz_user ur",'ur.User_Num = r.By_User_Num','LEFT')->group("R.By_User_Num")->field("r.User_Num, SUM(r.True_Num) number, r.By_User_Num , u.Hashrate, ur.User_Name, ur.Mobile")->select()->toArray();
    }

    /** 获取数量
     * @param $where
     * @return float|int
     */
    function getNmber($where){
        return $this::where($where)->sum("True_Num");
    }

    /** 修改数量
     * @param $where
     * @param array $data
     * @return $this
     */
    function UpNumber($where, $data = ['State'=>1,'True_Num'=>0]){
        return $this::where($where)->update($data);
    }
}