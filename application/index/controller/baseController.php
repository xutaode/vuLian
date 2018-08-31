<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/7/30
 * Time: 10:29
 */

namespace app\index\controller;
use app\common\service\regularVerify;
use think\Controller;

/**
 * 前端公共类
 * Class baseController
 * @package app\index\controller
 */
class baseController extends Controller
{
    /**
     * @var
     */
    protected $userNum;

    /**
     * 初始化
     * @return array
     */
    public function _initialize()
    {
        $this->userNum = cache(session_id()."_index");
        if (empty($this->userNum) && !session_id()){
            echo json_encode(array_combine(config("inputMsg"), array('1001', "登陆失效", false)));
            die();
        }
    }
}