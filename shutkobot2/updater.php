<?php
require_once('conf.php');

require_once(GENERAL_DIR.'adm/functions.php');
require_once('functions.php');

$sql=mysqli_query($dblink,"SELECT token,vkGroupId FROM `".DBP."vkApi`");
	  
$gtoken=array();
while(list($t,$gId)=mysqli_fetch_array($sql))
	$gtoken[$gId]=$t; 

$sql_l=mysqli_query($dblink,"SELECT mlistId FROM `".DBP."mlists`");

while (list($mlistId)=mysqli_fetch_array($sql_l)){

	$sql=mysqli_query($dblink,"SELECT startId,startDate FROM `".DBP."starts` WHERE isActive=1 AND mlistId={$mlistId}");
	   
	if(!list($startIdActive,$startDateActive)=mysqli_fetch_array($sql))
        list($startIdActive,$startDateActive)=array(-1000,date('Y-m-d 00:00:00'));

	$sql=mysqli_query($dblink,"SELECT count(finishId) FROM `".DBP."finishes` WHERE startId={$startIdActive}");
	   
	list($n_finishes)=mysqli_fetch_array($sql);

	$n_to_vote=min(3,$n_finishes);

	

	$sql=mysqli_query($dblink,"SELECT startId,startText FROM `".DBP."starts` WHERE isActive=0 AND startDate>='{$startDateActive}' AND startDate<='".date('Y-m-d H:i:s')."' AND mlistId={$mlistId} ORDER BY startDate DESC");

	if (list($startId,$startText)=mysqli_fetch_array($sql))
	{
	   //Сбрасываем состояния пользователей
	   //mysqli_query($dblink,"DELETE FROM `".DBP."userState` WHERE vkGroupId=0 ");
	    
	    //задаем новый активный зачин
	    
	    $text="Время новых шуток!
Сегодня на голосовании целых {$n_finishes}! После того, как проголосуешь хотя бы за {$n_to_vote} шутки, сможешь увидеть лучшие по итогам прошлого голосования.
Также уже доступен новый зачин: \"{$startText}\".
Голосуем, добиваем!";
	
	   mysqli_query($dblink,"UPDATE `".DBP."starts` SET isActive=1 WHERE startId={$startId}");
	   mysqli_query($dblink,"UPDATE `".DBP."starts` SET isActive=0 WHERE startId!={$startId} AND mlistId={$mlistId}");
	   
	   //подводим итоги голосования

	    $sql=mysqli_query($dblink,"SELECT startId FROM `".DBP."starts` WHERE isActive=0 AND startDate<'{$startDateActive}' AND mlistId={$mlistId} ORDER BY startDate DESC");
	   
	   list($startIdVote)=mysqli_fetch_array($sql);
	   
	   $sql=mysqli_query($dblink,"SELECT finishId FROM `".DBP."finishes` WHERE startId={$startIdVote}");

	    while (list($finishId)=mysqli_fetch_array($sql))
	    {
            list($avg,$med)=get_avg_med($finishId);
            
            mysqli_query($dblink,"UPDATE `".DBP."finishes` SET avgVote={$avg}, medVote={$med} WHERE finishId={$finishId}");
            
            $average[$finishId]=$avg;
            $median[$finishId]=$med;
		
	    }


	    $finishIdAverage = array_keys($average, max($average))[0];
	    $finishIdMed = array_keys($median, max($median))[0];
	    
	    mysqli_query($dblink,"UPDATE `".DBP."finishes` SET isBest=1 WHERE finishId={$finishIdAverage}");
	    mysqli_query($dblink,"UPDATE `".DBP."finishes` SET isBest=1 WHERE finishId={$finishIdMed}");
	   
	   //обновляем карму
	   $sql=mysqli_query($dblink,"SELECT AVG(vote) FROM `".DBP."votes` WHERE startId={$startIdVote}");
	   
	   list($avgVote_all)=mysqli_fetch_array($sql);
	   
	   $sql=mysqli_query($dblink,"SELECT vkId,finishId FROM `".DBP."finishes` WHERE startId={$startIdVote} ORDER BY avgVote DESC LIMIT 0,3");
	   
	   $best=array();
	   
	   while(list($vkId,$finishId)=mysqli_fetch_array($sql))
            $best[]=$vkId;
		
        $sql=mysqli_query($dblink,"SELECT vkId,finishId FROM `".DBP."finishes` WHERE startId={$startIdVote} ORDER BY medVote DESC LIMIT 0,3");
	  
	     while(list($vkId,$finishId)=mysqli_fetch_array($sql))
		$best[]=$vkId;
	   
	   
	   $sql=mysqli_query($dblink,"SELECT vkId FROM `".DBP."finishes` WHERE startId={$startIdVote} GROUP BY vkId");
	  
	  while(list($vkId)=mysqli_fetch_array($sql))
	  {
	     $sql_vkId=mysqli_query($dblink,"SELECT avgVote FROM `".DBP."finishes` WHERE vkId={$vkId} AND  startId={$startIdVote} ORDER BY avgVote DESC");
	  
	     list($avgVote)=mysqli_fetch_array($sql_vkId);
	     
	     //bot_debugger(implode(',',$best));
	     
	     if(in_array($vkId,$best))
	     {
            mysqli_query($dblink,"UPDATE `".DBP."rating` SET finishLimit=finishLimit+1 WHERE vkId={$vkId}");
            //bot_debugger("UPDATE `".DBP."rating` SET finishLimit=finishLimit+1 WHERE vkId={$vkId}");
	     }
	     
	     elseif($avgVote<$avgVote_all)
	     {
            mysqli_query($dblink,"UPDATE `".DBP."rating` SET finishLimit=finishLimit-1 WHERE vkId={$vkId} AND finishLimit>1");
            //bot_debugger("UPDATE `".DBP."rating` SET finishLimit=finishLimit-1 WHERE vkId={$vkId} AND finishLimit>1");
		
	     }
	     
	     
	  }
	     
	   
	   
	   //рассылаем оповещения
	   

	   
	   $sql=mysqli_query($dblink,"SELECT vkId,vkGroupId FROM `".DBP."db` WHERE (unsub=0 AND mlistId={$mlistId}) GROUP BY vkId");
	   
	   //echo "SELECT vkId,vkGroupId FROM `".DBP."db` WHERE (unsub=0 AND mlistId=1) GROUP BY vkId";
	   
	   while(list($vkId,$vkGroupId)=mysqli_fetch_array($sql))
	   {
	      $token=$gtoken[$vkGroupId];
	      
	      //echo $token.'<br>';
	      
	      $conf_str = file_get_contents ('conf.php');
	      $buttons_def_str=preg_replace('#.*//кнопки по умолчанию(.*)//конец кнопок по умолчанию.*#s','$1',$conf_str);
	      $sendResult=message_queue($token,$vkId,$text,date('Y-m-d H:i:00'),$buttons_def_str);
	      
	      if(!$sendResult[0])
          {
                if($sendResult[1]==901)
                {
                    user_mlist_manage($vkId,$mlistId,0,$vkGroupId[$vkId]);
                }
                
                mysqli_query($dblink,"DELETE FROM `".DBP_GENERAL."message_error`  WHERE token='{$token}' AND vkId={$vkId}");
            }
	      
	      //$res=vkApi_messagesSendButtons($vkId, $text,$buttons_def);
	   }
	   
	}
}

?>
