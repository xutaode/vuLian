<?php

namespace app\admin\controller;
use think\Controller;
/**
 * Class Common
 * @package app\admin\controller
 */
class Common extends Controller
{
    /**
     * @var
     */
    protected $mobile;
    /*
     * 初始化操作
    */
    public function _initialize($flg=false)
    {
        if (!$flg || $flg != 'index'){
            $this->mobile = cache(session_id()."_admin");
            if (!$this->mobile) $this->redirect("admin/index/login");
        }
    }
}



