<?php
/**
 * Created by PhpStorm.
 * User: Teluns
 * Date: 2018/7/31
 * Time: 13:27
 */

namespace app\index\controller;


use think\Controller;

class MomentsController extends  baseController
{




    function  getMessAgeCount()
    {
        $userNum = $this->userNum;

        if(strlen($userNum > 0 )) {

            return inputOut("", "0000",model("Moments")->getMessAgeCount($userNum));

        }
        //getMessAgeCount
    }

    /// 获取消息
    ///

    function  getMessAge()
    {    $userNum = $this->userNum;

        $page = input("page");


        if(strlen($userNum > 0 )) {


            $resut= model("Moments")->getMessage($this->userNum,$page); //getMomentsRepky("'123','123456'",$page);

            $reCout = 0;
            foreach ($resut as $item) {
                $repArr = array();

                $RepkyResut = model("Moments")->getMomentsRepky($item["Serial_No"], $page);
                if ($RepkyResut)
                {
                    $resut[$reCout]["reply"]=$RepkyResut;
                }else
                {
                    $resut[$reCout]["reply"]=$repArr;
                }

                $reCout=$reCout+1;
            }

            return inputOut("消息获取成功","0000",$resut);
        }


    }





    //点赞
    function  applyPraise(){

        $SerialNo = input("SerialNo");
        $type = input("type");
        $cancelType = input("cancelType");

        $iscancel=$cancelType ==1?true:false;
        $iscancel=$cancelType ==1?true:false;
        $userNum = $this->userNum;
        if(strlen($userNum > 0 )) {

            if (strlen($SerialNo) > 2) {
                if (model("Moments")->applyArticle($SerialNo, $userNum, $type,$iscancel)) {

                    if(!$iscancel)
                    return inputOut("点赞成功", "0000");
                    else
                        return inputOut("取消点赞成功", "0000");
                } else {
                    if(!$iscancel)
                        return inputOut("点赞失败", "1001");
                    else
                        return inputOut("取消点赞失败", "1001");
                }
            }
        }
        return inputOut("登陆失效","1001");
    }

    //评论
    function  comment(){

        $SerialNo = input("SerialNo");
        $type = input("type");
        $Content = input("Content");
        $Reply_UserNum= input("Reply_UserNum");
        $Acp_Reply_SerialNo= input("Acp_Reply_SerialNo");



        $userNum = $this->userNum;

        if(strlen($userNum > 0 ))
        {

            if(strlen($Content) > 1)
            {

                $resut= model("Moments")->getMomentsBySerialNo($SerialNo, $userNum);


                if (count($resut)> 0)
                {
                    $resut=$resut[0];

                    //.return inputOut($resut[0]["State"],"002221");


                    if ($resut["State"] == 1)
                    {


                        if ($type == 2)
                        {
                             if (strlen($Reply_UserNum) <2)
                             {
                                 return inputOut("回复数据异常","1006");;
                             }
                            if (strlen($Acp_Reply_SerialNo) <2)
                            {
                                return inputOut("回复数据异常","1006");;
                            }
                        }else
                        {
                            $type=1;
                        }

                        $replyNo= createdCode("order");
                        $Lost_Ip= $_SERVER['REMOTE_ADDR'];

                            //($Reply_SerialNo,$SerialNo,$UserNum,$Content,$Type,$Lost_Ip,$Reply_UserNum,$Acp_Reply_SerialNo)
                         $resut= model("Moments")->momentsReply($replyNo,$SerialNo,$userNum,$Content,$type,$Lost_Ip,$Reply_UserNum,$Acp_Reply_SerialNo);
                        if ($resut)
                        {
                            return inputOut("发布成功","0000");
                        }
                        else{
                            return inputOut("发布失败","0001");
                        }


                    }

                }

              //  $replyNo= getSerialNo();
               // model("Moments")->momentsReply($SerialNo);
            return inputOut("文章不存在， 或者已被删除","1004");

            }
            else
            {
                return inputOut("评论内容太少","1002");
            }
        }
        //$this->userNum

        return inputOut("登陆失效","1001");

    }
    ///发表
    function  publishArticle()
    {

        $Content = input("Content");


         if(strlen($Content) > 1)
         {
             $userNum = $this->userNum;
             if(strlen($userNum > 0 )) {



                 $SerialNo= createdCode("order");
                 $Lost_Ip= $_SERVER['REMOTE_ADDR'];
                  //publish($SerialNo,$User_Num,$Content,$Is_Top,$State,$LostIP)
                 $resut= model("Moments")->publish($SerialNo,$userNum,$Content,0,1,$Lost_Ip);

                 if ($resut)
                 {
                     return inputOut("发布成功","0000");
                 }
                 else{
                     return inputOut("发布失败","0001");
                 }


             }else
             {
                 return inputOut("登陆失效","1001");
             }


         }else
         {
             return inputOut("评论内容太少","1002");
         }


        return inputOut("登陆失效","1001");

    }

    function  getMomentsBySerialNo()
    {

        $SerialNo = input("SerialNo");
        $userNum = $this->userNum;
        $resut= model("Moments")->getMomentsBySerialNo($SerialNo,$userNum); //getMomentsRepky("'123','123456'",$page);

          if ( count($resut) > 0) {
              return inputOut("", "", $resut[0]);
          }
        return inputOut("失败", "9999", null);
    }


    /// 获取评论列表
    function  getCommentBySerialNo()
    {
        $SerialNo = input("SerialNo");
        $userNum = $this->userNum;
        $page =  input("Page");
         if (strlen($page) >  0)
         {
             if ($page <=0)
             {
                 $page=1;
             }

         }else
         {
             $page =1;
         }

     return  inputOut("","0000", model("Moments")->getCommentBySerialNo($SerialNo,$page,10,$userNum)); //getMomentsRepky("'123','123456'",$page);

    }


    // 获取矿工圈
    function  getMoments()
    {
        $page = input("page");

        $resut= model("Moments")->getmoments($page,$this->userNum); //getMomentsRepky("'123','123456'",$page);
        $SerialNos="";
        $cout=0;
        foreach ($resut as $item)
        {
            $SerialNos.=$item["Serial_No"].",";
            $resut[$cout]["reply"]="";
            $cout++;
        }
        if (strlen($SerialNos) > 0) {
            $SerialNos = substr($SerialNos, 0, strlen($SerialNos) - 1);

            $cout=0;
            foreach ($resut as $item) {
                $repArr= array();
                $reCout=0;
                $RepkyResut = model("Moments")->getMomentsRepky($item["Serial_No"], $page);
                foreach ($RepkyResut as $repky)
                {
                    if ($repky["SerialNo"] == $item["Serial_No"])
                    {
                        array_push($repArr,$repky);
                    }
                    $reCout++;
                }
                $resut[$cout]["reply"]=$repArr;
                $cout++;
            }
        }
        return inputOut("","",$resut);
    }

     // 删除动态


    function  deleteMoments()
    {
        $SerialNo = input("SerialNo");
        $userNum = $this->userNum;

        if (strlen($userNum) > 3){

            if (model("Moments")->deleteMoments($SerialNo,$userNum))
            {
                return  inputOut("删除成功","0000" );
            }else
            {
                return  inputOut("删除失败","0001" );
            }


        }
        else
        {

            return inputOut("登录失败","1001");

        }


    }

    // 获取矿工圈
    function  getMomentsByUserNum()
    {
        $page = input("page");

        $resut= model("Moments")->getmomentsByUserName($page,$this->userNum); //getMomentsRepky("'123','123456'",$page);
        $SerialNos="";
        $cout=0;
        foreach ($resut as $item)
        {
            $SerialNos.=$item["Serial_No"].",";
            $resut[$cout]["reply"]="";
            $cout++;
        }
        if (strlen($SerialNos) > 0) {
            $SerialNos = substr($SerialNos, 0, strlen($SerialNos) - 1);

            $cout=0;
            foreach ($resut as $item) {
                $repArr= array();
                $reCout=0;
                $RepkyResut = model("Moments")->getMomentsRepky($item["Serial_No"], $page);
                foreach ($RepkyResut as $repky)
                {
                    if ($repky["SerialNo"] == $item["Serial_No"])
                    {
                        array_push($repArr,$repky);
                    }
                    $reCout++;
                }
                $resut[$cout]["reply"]=$repArr;
                $cout++;
            }
        }
        return inputOut("","",$resut);
    }

}