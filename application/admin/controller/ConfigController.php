<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/7/26
 * Time: 17:19
 */

namespace app\admin\controller;
use think\Db;
use think\Exception;

/**
 * Class Config
 * @package app\admin\controller
 */
class ConfigController extends Common
{
    /** 获取列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    function getConfigList(){
        return inputOut("",'',model("config")->getConfigList());
    }

    /** 添加配置
     * @return array
     */
    function addConfig(){
        $param = input("post.");
        Db::startTrans();
        try{
            $result = model("config")->addConfig([
                'Code'               =>  $param['Code'],
                'Value'              =>  $param['Value'],
                'Desc_Val'          =>  $param['Desc_Val'],
            ]);
            if ($result <= 0)
                return inputOut("添加数据失败","0001");

            Db::commit();
            return inputOut();
        }catch (Exception $e){
            Db::rollback();
            return inputOut("代码异常","0001");
        }
    }
}