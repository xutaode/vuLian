<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    /**
     *    极客验证
     */
    'GEETESTID'     =>  '15afa71d8b6dc4342474cce9d80d44c1',
    'GEETESTKEY'    =>  'd009c718a7d9b50ec96e29e04c8db6c0',

    /**
     *      状态信息数组
     */
    'SMALLMSG'      =>  ['state','msg'],
    'MESSAGE'       =>  ['respCode','respDesc','respResult'],

    /**
     *      默认跳转对应的模板文件
     */
    'TMPL_ACTION_ERROR'      => 'Public:dispatch_jump',
    'TMPL_ACTION_SUCCESS'    => 'Public:dispatch_jump',

    /**
     *      加密文件        RSA 地址
     */
    'RSA_PUBLIC_PATH'       =>  "./keys/10004_public_key.pem",
    'RSA_PRIVATE_PATH'      =>  "./keys/10004_private_key.pem",

    /**
     *      加解类型
     */
    'en'         =>    '签名',
    've'         =>    '验签',
    'de'         =>    '解密',
    'ae'         =>    '加密',

];
