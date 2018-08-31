<?php
/**
 * Created by PhpStorm.
 * User: 京美电子
 * Date: 2018/8/2
 * Time: 16:13
 */
namespace app\common\service;
/**
 * 短信
 * Class ShortMessage
 * @package app\common\service
 */
class ShortMessage
{
    /**
     * 接口访问地址
     * @var string
     */
    private $url = "http://120.77.147.64:4219/ApiReq/Apigateway";
    /**
     * 接口身份id
     * @var string
     */
    private $Id = "10017";

    /**
     * 发送短信
     * @param $mobile
     * @param $session
     * @return bool
     */
    function sendMsg($mobile, $session='180'){
        $data = array(
            'requestNo'             =>  date("YmdHis").$mobile.rand(1000,9999),
            'partnerId'             =>  $this->Id,
            'transId'               =>  '26',
            'phone'                 =>  $mobile,
            'SendSmsTemplateCode' =>  "send",
            'SignName'              =>  "KCoin",
        );
        $result =  EncrpHttp($data, $this->url,'rsa',"keyPai");
        MyLog("发送手机短信：", $result);
        if ($result['respCode'] == "0000")
        {
            cache($mobile."time",null);
            cache($mobile,null);
            cache($mobile, $result['vercode'],$session);
            cache($mobile."time", time(),60);
            return false;
        }else{
            return $result['respDesc'];
        }
    }

    /**
     * 检验验证码
     * @param $mobile
     * @param $code
     * @return array|bool
     */
    function chickMsg($mobile, $code){
        if (!cache($mobile."time"))
            return "验证码有效期已过";
        if (cache($mobile) != $code)
            return "手机验证码有误";
        cache($mobile."time",null);
        cache($mobile,null);
        return false;
    }

}