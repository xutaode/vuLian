<?php
/**
 * Created by PhpStorm.
 * User: Teluns
 * Date: 2018/7/31
 * Time: 13:44
 */

namespace app\common\model;


use think\Model;

class Moments extends  Model
{

    //插入评论
    function  momentsReply($Reply_SerialNo,$SerialNo,$UserNum,$Content,$Type,$Lost_Ip,$Reply_UserNum,$Acp_Reply_SerialNo)
    {


            $moments = $this->query("select  * from xz_moments where Serial_No ='" . $SerialNo . "'  LIMIT 1");
            if ($moments)
            {
                $this->addMessage($SerialNo,$Content,1,$moments[0]["User_Num"],$UserNum,$Reply_SerialNo);

            }else{
                return false;
            }

             if ($Type == 1)
             {
                 $this::execute("UPDATE xz_moments set Comment_Num=Comment_Num+1 WHERE Serial_No ='".$SerialNo."'");


             }else
             {
                 $this::execute("UPDATE xz_moments_reply set Comment_Num=Comment_Num+1 WHERE Reply_SerialNo ='".$SerialNo."'");
                 $this::execute("UPDATE xz_moments set Comment_Num=Comment_Num+1 WHERE Serial_No ='".$SerialNo."'");
             }
             return $this::execute("INSERT INTO xz_moments_reply(Reply_SerialNo,SerialNo,UserNum,Content,State,Type,Is_Top,Like_Num,Lost_Ip,Reply_UserNum,Acp_Reply_SerialNo,add_time)          
            VALUES('".$Reply_SerialNo."','".$SerialNo."','".$UserNum."','".$Content."','1',$Type,1,0,'".$Lost_Ip."','".$Reply_UserNum."','".$Acp_Reply_SerialNo."',now())") > 0 ;
    }

    function getMomentsBySerialNo($SerialNo, $UserNum)
    {
        return  $this::query("select   m.Serial_No,m.Content,m.Add_Time,m.Comment_Num,m.Is_Top,m.Like_Num,m.Serial_No,m.User_Num,u.User_Name,l.State as islike ,m.State,u.Mobile from xz_moments m LEFT JOIN  xz_user as u on m.User_Num = u.User_Num  LEFT JOIN xz_moments_like as l on l.User_Num=  ".$UserNum."  and l.Serial_No=m.Serial_No  where  m.Serial_No ='".$SerialNo."'");

    }

    function  getCommentBySerialNo($SerialNo,$page,$count,$UserNum=1001)
    {


        return  $this::query("select m.SerialNo,m.UserNum,u.User_Name,m.Content,m.Add_Time ,m.Type,m.Is_Top,m.Like_Num,m.Comment_Num,m.Reply_SerialNo ,u2.User_Name as reUserName,l.State as islike  ,u.Mobile ,u2.Mobile as Reply_Mobile From xz_moments_reply as m  LEFT JOIN xz_user  as u  on m.UserNum = u.User_Num

      LEFT JOIN xz_user  as u2  on m.Reply_UserNum = u2.User_Num LEFT JOIN xz_moments_like as l on l.User_Num= ".$UserNum." and l.Serial_No=m.Reply_SerialNo where m.SerialNo='".$SerialNo."'ORDER BY m.Add_Time desc   LIMIT ".($page-1)*$count.",".$count);
    }


    ///发表
    function  publish($SerialNo,$User_Num,$Content,$Is_Top,$State,$LostIP)
    {

          //INSERT INTO xz_moments(Serial_No,User_Num,Content,Is_Top,Like_Num,Comment_Num,State,Add_Time,Up_Time)
        //
        //VALUES ('12325126','1001','测试看看',0,0,0,1,now(),now());

        return $this:: execute("INSERT INTO xz_moments(Serial_No,User_Num,Content,Is_Top,Like_Num,Comment_Num,State,Add_Time,Up_Time,LostIP) 
              VALUES ('".$SerialNo."','".$User_Num."','".$Content."',".$Is_Top.",0,0,".$State.",now(),now(),'".$LostIP."')") > 0 ;

    }



    //点赞
    function  applyArticle($SerialNo ,$userNum,$type,$iscancel=false)
    {


          $boolRetust= false;

           if ($iscancel )
           {
               if ($this::getapplyArticle($SerialNo,$userNum)==false) {
                   var_dump(123);
                   return false;
               }

           }else{
               if ($this::getapplyArticle($SerialNo,$userNum)) {

                   return false;
               }
           }



        // 文章点赞
         if($type ==1 ) {

             $moments = $this->query("select  * from xz_moments where Serial_No ='" . $SerialNo . "'  LIMIT 1");
             if ($moments)
             {
                 if ( $this->query("select COUNT(1) as _count from xz_moments_msg where type = 2 and Serial_No='".$SerialNo."' and By_User_Num='".$userNum."'")[0]["_count"] ==0)
                 {
                     $this->addMessage($SerialNo,"赞了我",2,$moments[0]["User_Num"],$userNum,"");
                 }


             }else{
                 return false;
             }

             if ($iscancel == false)
                 $boolRetust = $this::execute("UPDATE xz_moments set Like_Num=Like_Num+1 WHERE Serial_No ='" . $SerialNo . "'") > 0;
             else {
                 $boolRetust = $this::execute("UPDATE xz_moments set Like_Num=Like_Num-1 WHERE Serial_No ='".$SerialNo."'") > 0;
             }

         }
         //评论点赞
        if($type ==2 )
        {

            $moments_reply = $this->query("select  * from xz_moments_reply where Reply_SerialNo ='" . $SerialNo . "'  LIMIT 1");
            if ($moments_reply)
            {
                if ( $this->query("select COUNT(1) as _count from xz_moments_msg where type = 2 and Reply_SerialNo='".$SerialNo."' and By_User_Num='".$userNum."'")[0]["_count"] ==0) {
                $this->addMessage($SerialNo, "赞了我", 2, $moments_reply[0]["UserNum"], $userNum, $SerialNo);
            }

            }else{
                return false;
            }

            if ($iscancel==false)
            $boolRetust=  $this::execute("UPDATE xz_moments_reply set Like_Num=Like_Num+1 WHERE Reply_SerialNo ='".$SerialNo."'") > 0;
            else
            {
                $boolRetust=  $this::execute("UPDATE xz_moments_reply set Like_Num=Like_Num-1 WHERE Reply_SerialNo ='".$SerialNo."'") > 0;
            }
        }

         if ($boolRetust )
         {
             if($iscancel ==false){
                 $boolRetust=  $this::execute("INSERT into xz_moments_like(User_Num,Serial_No,State,Add_Time,Up_Time)  VALUES('".$userNum."','".$SerialNo."',1,now(),now())")>0;
             }
             else
             {
                 $boolRetust=  $this::execute("delete  from xz_moments_like where Serial_No='".$SerialNo."' and User_Num=".$userNum)>0;
             }
         }

        return $boolRetust;
    }


    function  getMessAgeCount($userNum)
    {

        return  $this::query("select count(1) as _count From xz_moments_msg where State=0 and  User_Num='".$userNum."'")[0]["_count"];
    }


    function  getMessage($userNum,$page=1)
    {
        $this::execute("UPDATE xz_moments_msg set State =1 WHERE  State =0 and  User_Num ='".$userNum."'");
        return   $this->query("select msg.Type,msg.Message_Content, u.User_Name as User_Name,msg.Add_Time,msg.Reply_SerialNo,m.Content,m.Serial_No,m.Add_Time as MomentsAddTime,u.Mobile,msg.Reply_SerialNo,msg.By_User_Num
        From xz_moments_msg as msg LEFT JOIN xz_user as u on u.User_Num= msg.By_User_Num LEFT JOIN xz_moments  as m on m.Serial_No=msg.Serial_No where msg.User_Num='".$userNum."'  order By msg.Add_Time desc  LIMIT ".($page-1).",10 ");
    }


    function  addMessage($Serial_No,$Message_Content,$type,$userNum,$ByUserNum,$Reply_SerialNo)
    {

        if ($userNum==$ByUserNum)
        {
            return false;
        }

       // var_dump("insert into xz_moments_msg(Serial_No,User_Num,By_User_Num,Message_Content,type,Add_Time) VALUES('".$Serial_No."',".$userNum.",".$ByUserNum.",'".$Message_Content."',".$type.",now())");
        return $this->execute("insert into xz_moments_msg(Serial_No,Reply_SerialNo,User_Num,By_User_Num,Message_Content,type,Add_Time) VALUES('".$Serial_No."','".$Reply_SerialNo."',".$userNum.",".$ByUserNum.",'".$Message_Content."',".$type.",now())") >0;
    }


    function  getapplyArticle($SerialNo,$userNum)
    {

        return ( $this::query("select count(1) as _count From xz_moments_like where Serial_No='".$SerialNo."' and User_Num='".$userNum."'")[0]["_count"]) > 0;
    }


     // 删除动态
        function  deleteMoments($SerialNo,$userNum)
        {
            return  $this::execute("UPDATE xz_moments set State  = 0 and Up_Time=now() where Serial_No='".$SerialNo."' and  User_Num =".$userNum)>0;

        }


     function  getmoments($page=1,$userNum=1001)
     {
         $reust= $this::query("select m.Id,m.Serial_No,m.Content,m.User_Num,u.User_Name,m.Is_Top,m.Like_Num,m.Comment_Num,m.Add_Time,l.State as islike ,u.Mobile From xz_moments as m LEFT JOIN xz_user as u on  m.User_Num = u.User_Num LEFT JOIN xz_moments_like as l on l.User_Num= ".$userNum." and l.Serial_No=m.Serial_No  where m.State= 1 ORDER BY m.Is_Top  desc ,m.Add_Time   desc  LIMIT ".($page-1).",10 ");
          return $reust;
     }

    function  getmomentsByUserName($page=1,$userNum=1001)
    {
        $reust= $this::query("select m.Id,m.Serial_No,m.Content,m.User_Num,u.User_Name,m.Is_Top,m.Like_Num,m.Comment_Num,m.Add_Time,l.State as islike ,u.Mobile From xz_moments as m LEFT JOIN xz_user as u on  m.User_Num = u.User_Num LEFT JOIN xz_moments_like as l on l.User_Num= ".$userNum." and l.Serial_No=m.Serial_No  where m.State= 1 and m.User_Num =".$userNum."  ORDER BY m.Is_Top  desc ,m.Add_Time   desc  LIMIT ".($page-1).",10 ");
        return $reust;
    }



     function getMomentsRepky($SerialNos,$page=1){

         $reust= $this::query("select m.Reply_SerialNo,m.SerialNo , m.Content ,m.Add_Time,m.Is_Top,m.UserNum,u.User_Name ,Type,m.Reply_UserNum ,r.User_Name as Reply_userName ,u.Mobile, r.Mobile as Reply_Mobile from xz_moments_reply  as m  LEFT JOIN xz_user as u on  m.UserNum  = u.User_Num 

      LEFT JOIN xz_user as ru on  m.Reply_UserNum  = ru.User_Num   LEFT JOIN xz_user as r on r.User_Num = m.Reply_UserNum

          WHERE m.SerialNo in (".$SerialNos.") ORDER BY m.Add_Time desc LIMIT ".($page-1).",5 ");


         return $reust;

     }

}