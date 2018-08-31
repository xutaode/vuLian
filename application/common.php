<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/** 输出
 * @param bool $msg
 * @param string $code
 * @param null $data
 * @param null $type
 * @return array
 */
function inputOut($msg=false, $code='0000', $data=null, $type='json',$maxpage=0,$sizeData=0){
    $msg = $msg ? $msg: "操作成功";
    $code = ($code && $code !== '0000') ? $code: "0000";
    $result = array_combine(config("inputMsg"), array($code, $msg, $data));
    $result['Nowcount'] = $data ? count($data) : 0;
    $result['maxpage'] = $maxpage;
    if (is_array($sizeData)){
        $result['count'] = $sizeData[0];
        $result['sizeData'] = $sizeData[0];
    }else{
        $result['sizeData'] = $sizeData;
    }
    if ($type == 'json' || $type == '')
        return json($result);
    return $result;
}

/**
 * 获取浮点数    （不四舍五入）
 * @param $num
 * @param int $scale
 * @return string
 */
function getNum($num, $scale = 2) {
    $numStr = (string)$num . str_repeat('0', $scale);

    //匹配精度前的数值
    if(preg_match('#^\d+\.\d{0,'.$scale.'}#', $numStr, $match)) {
        return $match[0];
    } else {
        return '0';
    }
}

/**
 * 通过下标获取新数组
 * @param $array
 * @param $field
 * @param $type
 * @return array
 */
function FromFieldGetArray($array, $field, $type='get'){
    $result = array();
    if (is_array($array)){
        switch ($type){
            case 'get':
                foreach ($array as $k => $v)  $result[$v[$field]] = $v;
                break;
            case 'delet':
                foreach ($array as $k => $v)
                    foreach ($v as $kk => $vv){
                        if (in_array($kk, $field)) unset($array[$k][$kk]);
                    }
                $result = $array;
                break;
            case 'merge':
                foreach ($array as $k => $v)
                    $result[$v[$field]][] = $v;
                break;
            default:
                $result = $array;
                break;
        }
    }
    return $result;
}

/**
 * 获取前端用户信息
 * @param $user
 * @return mixed
 */
function getCatchUser($user){
    $user = cache($user."_mining_index");
    if (!$user)
        $user = model("User")->getUserList(['User_Num'=>$user])[0];
    return $user;
}

/**
 * 生成提现地址
 * @param $value
 * @return mixed
 */
function getAddress(){
    $value = createdCode('address');
    $cache = cache("AddressCashe");
    if (!$cache){
        $cache = array();
        $userAddress = model("User")->getUserList();
        foreach ($userAddress as $v) array_push($cache, $v['address']);
        cache("AddressCashe", $cache,86400);
    }
    if (!in_array($value, $cache)){
        array_push($cache, $value);
        cache("AddressCashe", $cache,86400);
        $code = $value;
    }else
        $code = findAddress();
    return $code;
}

/**
 * 生成各种码
 * @param $flg
 * @return bool|int|mixed
 */
function createdCode($flg='user'){
    $code = cache($flg."Code");
    switch ($flg){
        case 'user':
            if (!$code){
                $code = db('User')->Max("User_Num");
                cache("userCode",$code);
            }
            return $code + rand(0,1000);
            break;
        case 'Invite':
            $code="ABCDEFGHIGKLMNOPQRSTUVWXYZ";
            $rand=$code[rand(0,25)].strtoupper(dechex(date('m')))
                .date('d').substr(time(),-5)
                .substr(microtime(),2,5).sprintf('%02d',rand(0,99));
            for(
                $a = md5( $rand, true ),
                $s = '0123456789ABCDEFGHIJKLMNOPQRSTUV',
                $d = '',
                $f = 0;
                $f < 8;
                $g = ord( $a[ $f ] ), // ord（）函数获取首字母的 的 ASCII值
                $d .= $s[ ( $g ^ ord( $a[ $f + 8 ] ) ) - $g & 0x1F ],  //按位亦或，按位与。
                $f++
            );
            return $d;
            break;
        case 'address':
            $key = "";
            $pattern = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLOMNOPQRSTUVWXYZ';
            for($i=1;$i<35;$i++) $key .= $pattern{mt_rand(0,35)};
            return $key;
            break;
        case 'order':
            return date("YmdHis").rand(10000, 99999);
            break;
        default:
            return false;
            break;
    }
}

/**
 * 加密请求类
 * @param $url                      传送的地址
 * @param $data                     传送的数据
 * @param string $enthod            加密方式   md5  sha1  rsa  Des
 * @param int $rank                 是否排序    1不排序   其他为排序函数
 * @param int $direction            添加私钥的方向   1前    2后
 * @param string $method            传输的方式   Post   Get
 * @param array $param              传输的参数
 * @param int $original             是否写日志   1不写  2都写入  3加密写 请求不写   4请求写 加密不写
 * @return string
 */
function EncrpHttp($data, $url = '1', $enthod='rsa',$rank='ksort', $direction=2, $method='Post', array $param = array(), $original = 4)
{
    try
    {
        $key = strtoupper($enthod);
        $private_key =  config("Encrpytion.{$key}")['r'];
        $public_key =  config("Encrpytion.{$key}")['u'];


        /*------   引用Encryption类文件     ------*/
        $Encrpytion =\CustomTool\Encryption::init($enthod,$direction,$private_key,$public_key);

        if (!$Encrpytion)
            exception('加载加密类失败,没有该加密方式',0001,null);

        /*------   是否排序     ------*/
        if ($rank != 1)
        {
            if ($rank == 'keyPai'){
                $key = array_keys($data);natcasesort ($key);foreach ($key as $v){$result[$v] = $data[$v];}$data = $result;
            }else{
                $fun = (string)$rank; $fun($data);
            }
        }
        /*------   判断是否为数组     ------*/
        if (is_array($data)){
            /*-----  去空格、空值、拼接字符串   -------*/
            foreach($data as $k => $v){if(isset($v)){$Encrpydata[$k] = trim($v," ");}}
            $ableendata = urldecode(http_build_query($Encrpydata));
        }else $ableendata = $data;

        /*------   签名     ------*/
        if ($original == 2 || $original == 3) { MyLog('签名原始数据',$ableendata,'Encrp'); }
        $data['signature'] = $Encrpytion->Sign($ableendata);

        if (!$data['signature'])
            exception('签名失败',0002,null);

        $url = ($url == 1) ? config("ApiPath"): $url;
        /*------   引用curl类文件     ------*/
        $Curl = \CustomTool\Curl::init($url);

        if (!in_array(ucfirst(strtolower($method)),['Post','Get']))
            exception('没有该请求方式',0004,null);

        $response = $Curl->$method($data,$param);

        parse_str($response,$result);
        /*------   记录原始数据     ------*/
        if ($original == 2 || $original == 4)
            MyLog('发送原始数据',urldecode($Curl->get_info()),'Curl');

        /*-----   返回数据记录      -----*/
        $transId = $data['transId'] ? $data['transId']: '00';
        $desc = config("APIMsg.transId")[$transId];
        MyLog($desc,urldecode($response),'EncrpHttp');


        /*-----     验签   -----*/
        $signature = $result['signature'];
        unset($result['signature']);
        if ($Encrpytion->Verify(urldecode(http_build_query($data)),$signature))
            exception('验签错误',9999,null);

        return $result;
    }
    catch (\think\Exception $e)
    {
        \think\Log::write("自定义日志函数错误【 EncrpHttp 】：".$e->getMessage(),'error');
        return ['respCode' => $e->getCode(),'respDesc' =>$e->getMessage()];
    }
}

/**
 * 自定义日志函数
 * @param $desc         说明
 * @param $msg          内容
 * @param string $level 标识
 * @param string $path  地址
 */
function MyLog($desc ,$msg, $level = 'INFO', $path = '')
{
    try
    {
        $path = is_dir($path) ? $path.date("Ymd").".log": $path;
        // 写入地址
        $logpath = ($path=='') ? config("MyLog.CusLogPath") : $path;
        $logdir = dirname($logpath);
        !is_dir($logdir) && mkdir($logdir, 0777, true);

        // 判断文件大小
        $size = is_file($logpath) ? filesize($logpath) : 0;
        $orgsize = config("MyLog.file_size");
        if ($orgsize > 0 && ($size > $orgsize)) $logpath = $logdir.date('Y_m_d_H').".log";

        //  日志格式
        $now     = date("Y-m-d H:i:s");
        $ip      = request()->ip();
        $method  = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'CLI';
        $uri     = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        if (is_array($msg)) $data = "【".$level."】：".$desc."   数据：".json_encode($msg,JSON_UNESCAPED_UNICODE ).PHP_EOL; else $data = "【".$level."】：".$desc." ".$msg;
        $message = rtrim("[{$now}] {$ip} {$method} {$uri}\r\n" . $data,"\r\n").PHP_EOL;
        //写入
        error_log($message, 3, $logpath);
    }catch (Exception $e)
    {
        \Think\Log::write("自定义日志函数错误【 MyLog 】：".$e->getMessage(),'notice');
    }
}


