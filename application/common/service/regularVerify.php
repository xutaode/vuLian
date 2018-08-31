<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/8/9
 * Time: 13:53
 */

namespace app\common\service;
use think\Validate;

/**
 * 正则验证
 * Class regularVerify
 * @package app\common\service
 */
class regularVerify extends Validate
{
    /**
     * 规则
     * @var array
     */
    protected $rule = [
        'tel|手机号码'          =>  'require|number|length:11',
        'pwd|密码'               =>   'require|alphaDash|min:6|max:16',
        'com_pwd|确认密码'      =>  'require|confirm:pwd',
        'verify_code|图片验证码'   =>  'require|number|length:4',
        'v_code|短信验证码'     =>  'require|number|length:5',
        'Invitation_code|邀请码'=> 'alphaNum|length:8',
        'id|数据id'                 =>     'require|number|min:1|max:10',
        'type|类型'               =>    'require|number',


        'appId|应用id'           =>    'alphaDash|length:11',
        'passwd|密码'            =>    'alphaDash|min:6|max:16',
        'key|应用key'            =>    'alphaNum',
        'phone|用户名'           =>    'number|length:11',
        'name|昵称'               =>    'alphaDash',
        'page|页码'               =>    'number|min:1|max:5',
        'city|城市名称'           =>     'chsAlpha',
        'typeId|统计分类ID'      =>     'number|min:1|max:5',
        'appKey|应用key'          =>     'alphaNum',
        'count|统计次数'          =>     'number|min:1|max:5',
    ];
    /**
     * 错误提示
     * @var array
     */
    protected $message = [
        'region.eq'  =>  '请选择代理商',
    ];
    /**
     * 场景
     * @var array
     */
    protected $scene = [
        'login'       =>  ['tel','pwd'],
        'register'   =>  ['tel','pwd','com_pwd','v_code','Invitation_code'],
        'SendCode'   =>  ['tel','verify_code'],
    ];
}

//$validate = new regularVerify;
//if (!$validate->scene("api")->check($postdata))
//    exception($validate->getError());