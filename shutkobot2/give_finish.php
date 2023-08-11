<?php

$userState='give_finish';

   $sql=mysqli_query($dblink,"SELECT startId FROM `".DBP."starts` WHERE isActive=1");
   
   list($startId)=mysqli_fetch_array($sql);

   $sql=mysqli_query($dblink,"SELECT finishLimit FROM `".DBP."rating` WHERE vkId={$vkId}");
   
   if(!mysqli_num_rows($sql))
   {
      mysqli_query($dblink,"INSERT INTO `".DBP."rating` (vkId) VALUES ({$vkId})");
      $sql=mysqli_query($dblink,"SELECT finishLimit FROM `".DBP."rating` WHERE vkId={$vkId}");
   }
   
   list($finishLimit)=mysqli_fetch_array($sql);
   
   $sql=mysqli_query($dblink,"SELECT count(vkId) FROM `".DBP."finishes` WHERE vkId={$vkId} AND startId={$startId}");
   
//vkApi_messagesSend(ADM_VK_ID, "INSERT INTO `".DBP."finishes` (startId,finishText,vkId) VALUES ({$startId},'{$incommingText}',{$vkId})");

   list($nFinishes)=mysqli_fetch_array($sql);

//vkApi_messagesSend(ADM_VK_ID, "SELECT count(vkId) FROM `".DBP."finishes` WHERE vkId={$vkId} AND startId={$startId}");


if ($incommingText!='Добить')
{

   
   if($nFinishes<$finishLimit)
   {
   mysqli_query($dblink,"INSERT INTO `".DBP."finishes` (startId,finishText,vkId) VALUES ({$startId},'{$incommingText}',{$vkId})");

//vkApi_messagesSend(ADM_VK_ID, "INSERT INTO `".DBP."finishes` (startId,finishText,vkId) VALUES ({$startId},'{$incommingText}',{$vkId})");

   $replyText='Добивка записана, можешь добавить еще одну';
   }
   
   else
   {
      $replyText='Сегодня твой лимит на добивки закончился:( Добивка не сохранена';
   }

}

else
{
   
   $sql=mysqli_query($dblink,"SELECT startText FROM `".DBP."starts` WHERE startId={$startId}");
   
   list($startText)=mysqli_fetch_array($sql);
   
   $replyText="Добей шутку: {$startText}";
}


$replyText.="

P.S.: Сегодня ты можешь предложить еще ".($finishLimit-$nFinishes)." добивок";

?>
