<?php



if (!isset($_REQUEST)) {
    return;
}

require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/adm/functions.php');

//Получаем и декодируем уведомление

if (isset($_GET['emulate']))
{
   if(gethostbyname($_SERVER['SERVER_NAME'])!=$_SERVER['REMOTE_ADDR'])
   	exit;
   
   $data = json_decode($_POST[0], true);
}
else
   $data = json_decode(file_get_contents('php://input'), true);

   //log_msg(file_get_contents('php://input'));

//log_msg($data);




if($data['callback_query'])
{
    $payloadText=$data['callback_query']['data'];    
    $data = @$data['callback_query']['message'];
    $incommingText=$data['text'];
    

}

else
{
    $data = $data['message'];
    $incommingText=$data['text'];
    $payloadText='';
}



$vkId = @$data['chat']['id'];

if(!$data['chat']['first_name'])
{
    if($data['chat']['last_name'])
    {
        $data['chat']['first_name']=$data['chat']['last_name'];
        $data['chat']['last_name']='';
    }
    else
    {
        $data['chat']['first_name']='Дорогой друг';
    }
}
//log_msg($data);

if (isset($_GET['bot_id']))
{
    $currentGroupId=$_GET['bot_id'];
    //bot_debugger('I know bot_id!!! '.$_GET['bot_id']);
}
elseif (isset($_GET['botId']))
    $currentGroupId=$_GET['botId'];
else
    $currentGroupId=DEF_BOT_ID;

$sql=mysqli_query($dblink,"SELECT token,confirmToken,secret FROM `".DBP."vkApi` WHERE vkGroupId={$currentGroupId}");
list($token,$confirmationToken,$secretKey)=mysqli_fetch_array($sql);




if (($incommingText=='Начать') OR ($incommingText=='Start') OR ($incommingText=='начать') OR ($incommingText=='start')  OR (stristr($incommingText, '/start')) )
        {
          //bot_debugger("Start payload: {$payloadText}");
         
         
         $payloadText = 'start';
          
          mysqli_query($dblink,"INSERT INTO `".DBP_GENERAL."userInfo` (vkId,sex,first_name,last_name,full_name,username) VALUES ({$vkId},0,'{$data['chat']['first_name']}','{$data['chat']['last_name']}','".full_name($data['chat']['first_name'])."','{$data['chat']['username']}')");
        }
        
        if (($incommingText=='test_by_admin'))
        {
          //bot_debugger("Start payload: {$payloadText}");
          $payloadText = 'test_by_admin';
        }
        

        
        if (user_auth($vkId)) //Нового пользователя - подписываем на рассылку и отправляем первое сообщение что бы он там ни написал
        {
          //bot_debugger("Start payload: {$payloadText}");
          $payloadText = 'start';
          
         
        }
        
        if (stristr($incommingText, '/start ')) //но если переданы параметры через ссылку, то передаем их в соответствующий payloadText, даже если это первый запуск бота
         {
            
            list($tmp,$payloadText) = explode(' ', $incommingText);
            
            $payloadText=str_replace('_',':',$payloadText);
            
         }
        
        
        mysqli_query($dblink,"UPDATE `".DBP_GENERAL."userInfo` SET first_name='{$data['chat']['first_name']}', last_name='{$data['chat']['last_name']}', username='{$data['chat']['username']}', full_name='".full_name($data['chat']['first_name'])."' WHERE vkId={$vkId}");
        
        if(!$payloadText) //Проверяем, нет ли у пользователя текущего состояния, которое задает payloadText; Также проверяем, не является ли текст командой из меню
        {
           $sql=mysqli_query($dblink,"SELECT payloadText FROM `".DBP."userState` WHERE vkId={$vkId} AND vkGroupId IN (0,{$currentGroupId})");
           while (list($pT)=mysqli_fetch_array($sql))
              $payloadText=$pT;
            
            $sql=mysqli_query($dblink,"SELECT payloadText FROM `".DBP."keyboard` WHERE vkId={$vkId} AND buttonText='{$incommingText}'");
           while (list($pT)=mysqli_fetch_array($sql))
              $payloadText=$pT;
        }
  
        
        if (stristr($payloadText,':'))
        {
          $pt=stristr($payloadText,':',1);
          $payloadParams=str_replace($pt.':','',$payloadText);
          $payloadText=$pt;
        }
        
        else
            $payloadParams='';
        
        
        if (($incommingText=='меню') OR ($incommingText=='Меню'))
        {
          //bot_debugger("Start payload: {$payloadText}");
          $payloadText = 'menu';
        } 
      
      
      switch ($payloadText)
        {
          
           case "menu":
            
             $sql=mysqli_query($dblink,"SELECT buttonText FROM `".DBP."keyboard` WHERE vkId={$vkId}");
             
             if(!mysqli_num_rows($sql))
                bot_debugger("Ошибка вызова меню для {$vkId}");
            else
            {
                $text="Для использования команды вместо кнопки просто скопируйте и отправьте текст команды в ответ. Вам на данный момент доступны следующие команды:";
                
                //log_msg(vkApi_messagesSend($vkId, $text));
                
                while (list($buttonText)=mysqli_fetch_array($sql))
                    vkApi_messagesSend($vkId, $buttonText);
            }
             
             
             user_mlist_manage($vkId,$payloadParams,1);
             
             $text='Вы успешно подписаны на рассылку: "'.$mlistName[$payloadParams].'"!';
             
             
          
          break;
           
           
           case "subscription":
             
             if ($payloadParams)
                $n_start=$payloadParams;
            else
                $n_start=0;
             
             $sql=mysqli_query($dblink,"SELECT mlistId,mlistName FROM `".DBP."mlists`");
             while (list($mlistId,$name)=mysqli_fetch_array($sql))
                $mlistName[$mlistId]=$name;
             
             $sql=mysqli_query($dblink,"SELECT mlistId,unsub FROM `".DBP."db` WHERE vkId={$vkId} AND vkGroupId IN (0,{$currentGroupId}) GROUP BY mlistId");
             
             $lists_str='-1';
             $sub_str='';
             $unsub_str='';
             $sub_buttons=array();
             $unsub_buttons=array();
             $s=0;
             $us=0;
             $k=1;
             
             while (list($mlistId,$unsub)=mysqli_fetch_array($sql))
             {
                $lists_str.=','.$mlistId;
                
                if ($unsub)
                {
                   $unsub_str.='
- '.$mlistName[$mlistId];
                   
                   $sub_buttons[$s/$k][$s%$k][0]["payload"]="sub:".$mlistId;
                   $sub_buttons[$s/$k][$s%$k][1]='Подписаться: '.$mlistName[$mlistId];
                   $sub_buttons[$s/$k][$s%$k][2]='positive';
                   
                   $s++;
                   

                }
                
                else
                {
                   $sub_str.='
- '.$mlistName[$mlistId];
                   
                   $unsub_buttons[$us/$k][$us%$k][0]["payload"]="unsub:".$mlistId;
                   $unsub_buttons[$us/$k][$us%$k][1]='Отписаться: '.$mlistName[$mlistId];
                   $unsub_buttons[$us/$k][$us%$k][2]='negative';
                   
                   $us++;
                }
             }
             
             $sql=mysqli_query($dblink,"SELECT mlistId FROM `".DBP."mlists` WHERE isPublic=1 AND vkGroupId IN (0,{$currentGroupId}) AND mlistId NOT IN ({$lists_str})");
                          
             while (list($mlistId)=mysqli_fetch_array($sql))
             {
               $unsub_str.='
- '.$mlistName[$mlistId];
                   
               $sub_buttons[$s/$k][$s%$k][0]["payload"]="sub:".$mlistId;
               $sub_buttons[$s/$k][$s%$k][1]='Подписаться: '.$mlistName[$mlistId];
               $sub_buttons[$s/$k][$s%$k][2]='positive';
                   
               $s++;
             }
             
             if(!$sub_str)
                $sub_str='
                (пусто)';
             
             if(!$unsub_str)
                $unsub_str='
                (пусто)';
                
             $text='Здравствуйте, %name%!
На данный момент вы подписаны на следующие рассылки:'.$sub_str.'

Список рассылок, на которые вы можете подписаться дополнительно:'.$unsub_str.'

Для управления подпиской используйте кнопки ниже.';

             
             
             $subscription_buttons=array_merge($sub_buttons,$unsub_buttons);
             $n=sizeof($subscription_buttons);



             if($n>7)
             {
                 
                
                for($i=$n_start;$i<$n_start+7;$i++)
                {
                    
                    if (!isset($subscription_buttons[$i][0][0]["payload"]))
                        break;
                    $buttons[$i]=$subscription_buttons[$i];
                }
                
                
                
                if($i==($n_start+7))
                {
                    $buttons[$i][0][0]["payload"]="subscription:".($n_start+7);
                    $buttons[$i][0][1]='Следующие';
                    $buttons[$i][0][2]='positive';
                }
                else
                {
                    $buttons[$i][0][0]["payload"]="subscription";
                    $buttons[$i][0][1]='Сначала';
                    $buttons[$i][0][2]='positive';
                }
                    
             
             }
             else
                $buttons=$subscription_buttons;
                
             //$str=print_r($sub_buttons,TRUE);   
             //bot_debugger($str);
             
             $txt=personal_text($vkId, $text);
             vkApi_messagesSendButtons($vkId, $txt[$vkId],array_merge($buttons,$buttons_def));
           
          break;
          
          case "sub":
            
             $sql=mysqli_query($dblink,"SELECT mlistId,mlistName FROM `".DBP."mlists`");
             while (list($mlistId,$name)=mysqli_fetch_array($sql))
                $mlistName[$mlistId]=$name;
             
             user_mlist_manage($vkId,$payloadParams,1);
             
             $text='Вы успешно подписаны на рассылку: "'.$mlistName[$payloadParams].'"!';
             
             vkApi_messagesSendButtons($vkId, $text,array_merge($buttons,$buttons_def));
          
          break;
          
          case "unsub":
          
             $sql=mysqli_query($dblink,"SELECT mlistId,mlistName FROM `".DBP."mlists`");
             while (list($mlistId,$name)=mysqli_fetch_array($sql))
                $mlistName[$mlistId]=$name;
             
             if(user_mlist_manage($vkId,$payloadParams,0))
             	$text='Вы успешно отписаны от рассылки: "'.$mlistName[$payloadParams].'"!';
             else
             	$text='Не удалось отменить подписку на рассылку: "'.$mlistName[$payloadParams].'", т.к. Вы уже отписались ранее или подписывались на нее через другое сообщество.';
             vkApi_messagesSendButtons($vkId, $text,array_merge($buttons,$buttons_def));
             
             //vkApi_messagesSend(ADM_VK_ID, "user_mlist_manage($vkId,$payloadParams,0);");
          
          break;
          
          case 'get_promo':
             $sql=mysqli_query($dblink,"SELECT pcTemplatePHP FROM `".DBP."promocodeTemplates` WHERE pcTemplateName='{$payloadParams}'");
            
             if(list($pcTemplatePHP)=mysqli_fetch_array($sql))
             {
                eval($pcTemplatePHP);
                
                
                mysqli_query($dblink,"INSERT INTO `".DBP."promocodes` (promoCode,finDate,activationsN,actionPHP) VALUES ('".mysqli_escape_string($dblink,$promoCode)."','{$finDate}',{$activationsN},'".mysqli_escape_string($dblink,$actionPHP)."')");
                
                if(!$replyText)
                   $replyText=$promoCode;
                
                
                
                $buttons[1][0][0]["payload"]="get_promo:".$payloadParams;
                $buttons[1][0][1]="Получить промо-код еще раз";
                $buttons[1][0][2]="primary";
                $txt=personal_text($vkId, $replyText);
                vkApi_messagesSendButtons($vkId, $txt[$vkId],array_merge($buttons,$buttons_def),$attachments);
             }
             
             else
             {
                $buttons[1][0][0]["payload"]="get_promo:".$payloadParams;
                $buttons[1][0][1]="Получить промо-код еще раз";
                $buttons[1][0][2]="primary";
                
                vkApi_messagesSendButtons($vkId, 'Не удалось получить промокод, попробуйте позже или дождитесь ответа администратора',array_merge($buttons,$buttons_def),$attachments);
                
                bot_debugger("Ошибка при выдаче промокода: "."SELECT pcTemplatePHP FROM `".DBP."promocodeTemplates` WHERE pcTemplateName='{$payloadParams}'");
                
             }
          break;
          
          case 'vote':
            

            if (stristr($payloadParams,',')) //требуется поправить!!!
            {
                
                
                
                list($vote,$pollId)=explode(',',$payloadParams);
                
                $sql=mysqli_query($dblink,"SELECT date FROM `".DBP."vote` WHERE vkId={$vkId} AND pollId={$pollId}");
                
                if(list($date)=mysqli_fetch_array($sql))
                {
                    if(strtotime($date)+24*60*60>time())
                    {
                        mysqli_query($dblink,"UPDATE `".DBP."vote` SET vote={$vote} WHERE vkId={$vkId} AND pollId={$pollId}");
                        
                        $replyText="%name%, голос обновлен, новая оценка - {$vote}! Благодарим за участие в опросе!
Чтобы изменить оценку, достаточно нажать на другую кнопку с нужной оценкой (поменять голос возможно в течение суток после отправки первой оценки).";
                    }
                    
                    else
                    {
                        $replyText="%name%, к сожалению изменить оценку можно только в течение суток, и этот срок прошел. Оценка не сохранена.";
                    }
                }
                
                else
                {
                
                mysqli_query($dblink,"INSERT INTO `".DBP."vote` (vkId,pollId,date,vote) VALUES ({$vkId},{$pollId},'".date('Y-m-d H:i:s')."',{$vote})");
                
                $replyText="%name%, голос c оценкой {$vote} учтен! Благодарим за участие в опросе!
Чтобы изменить оценку, достаточно нажать на другую кнопку с нужной оценкой (поменять голос возможно в течение суток после отправки первой оценки).";
                
                }

            }
            
            else
                bot_debugger('Error in vote!');

            

            $txt=personal_text($vkId, $replyText);
            vkApi_messagesSendButtons($vkId, $txt[$vkId],array_merge($buttons,$buttons_def),$attachments);
          break;
          
          //блок действий для мероприятий
          
          case "events_info":
             $nowDate=date('Y-m-d');
            $eventId=$payloadParams;

            $sql=mysqli_query($dblink,"SELECT eventFinish,eventFinishTime,eventPreregStart,eventRegStart,eventPreregInfo,eventPreregPHP,eventRegInfo,eventRegPHP FROM `".DBP."events` WHERE eventId={$eventId}");
            


            list($eventFinish,$eventFinishTime,$eventPreregStart,$eventRegStart,$eventPreregInfo,$eventPreregPHP,$eventRegInfo,$eventRegPHP)=mysqli_fetch_array($sql);

            if ($eventFinish<$nowDate)
            {
               $replyText='Мероприятие уже прошло, регистрация надоступна. Перезапустите бота по кнопке ниже, чтобы начать сначала.';
            }

            elseif ($eventRegStart<$nowDate)
            {
               $replyText=$eventRegInfo;
               eval($eventRegPHP);
            }

            else
            {
               $replyText=$eventPreregInfo;
               eval($eventPreregPHP);
            }
            
            $txt=personal_text($vkId, $replyText);
            vkApi_messagesSendButtons($vkId, $txt[$vkId],array_merge($buttons,$buttons_def),$attachments);
            
          break;
          
          case "events_reg":
             $nowDate=date('Y-m-d');

            list($eventId,$roleId)=explode(';',$payloadParams);

            user_eventreg_manage($vkId,$eventId,$roleId,1,$currentGroupId); //vkId,eventId,roleId,action,group

            $sql=mysqli_query($dblink,"SELECT roleMessage,rolePHP FROM `".DBP."events_roles` WHERE eventId={$eventId} AND roleId={$roleId}");
            



            list($roleMessage,$rolePHP)=mysqli_fetch_array($sql);

            $replyText=$roleMessage;
            
            eval($rolePHP);
            
            $txt=personal_text($vkId, $replyText);
            vkApi_messagesSendButtons($vkId, $txt[$vkId],array_merge($buttons,$buttons_def),$attachments);
          break;
           
          case "events_unreg":
             $nowDate=date('Y-m-d');

            list($eventId,$roleId)=explode(';',$payloadParams);
            $roleId_arr=explode(',',$roleId);

            user_eventreg_manage($vkId,$eventId,$roleId,0,$currentGroupId); //vkId,eventId,roleId,action,group

            $replyText='Вы успешно отменили регистрацию. Можете зарегистрироваться снова, используя кнопку ниже.';


            $buttons_n=2;
            $s=0;

            $buttons[$s/$buttons_n][$s%$buttons_n][0]["payload"]="events_info:{$eventId}";
            $buttons[$s/$buttons_n][$s%$buttons_n][1]="Снова зарегистрироваться";
            $buttons[$s/$buttons_n][$s%$buttons_n][2]='positive';
            
            $txt=personal_text($vkId, $replyText);
            vkApi_messagesSendButtons($vkId, $txt[$vkId],array_merge($buttons,$buttons_def),$attachments);
            
          break; 
          
          
          
          default:
          message_log(-1,$token,$vkId,$incommingText,date("Y-m-d H:i:s"));
            if (!$payloadText)
            {
                              
               // Проверка, является ли сообщение промо-кодом
               $sql=mysqli_query($dblink,"SELECT finDate, activationsN,activatedN, actionPHP FROM `".DBP."promocodes` WHERE LOWER(promoCode)='".mb_strtolower($incommingText)."' ORDER BY finDate DESC");
               
               if(list($finDate, $activationsN,$activatedN, $actionPHP)=mysqli_fetch_array($sql))
               {
                   
                   $promocodeWorks=0;
                   
                   
                   if($finDate<date('Y-m-d H:i:s'))
                   {
                      $replyText='Cрок действия данного промо-кода истек - попробуйте использовать другой.';
                   }
                   
                   elseif(($activationsN)&($activationsN<=$activatedN))
                   {
                      $replyText='Данный промо-код уже был активирован максимально возможное число раз - попробуйте использовать другой.';
                   }
                   
                   else
                   {
                      $promoCode=$incommingText;
                      eval($actionPHP);
                   }
                   
                   if (isset($replyText))
                      if($replyText)
                      {
                         $txt=personal_text($vkId, $replyText);
                         vkApi_messagesSendButtons($vkId, $txt[$vkId],array_merge($buttons,$buttons_def),$attachments);
                      }
                      
                   if ($promocodeWorks)
                      mysqli_query($dblink,"UPDATE `".DBP."promocodes` SET activatedN=".($activatedN+1)." WHERE LOWER(promoCode)='".mb_strtolower($incommingText)."' AND finDate='{$finDate}'");
               }
               
               else
               {
                $user_info=vkApi_usersGet($vkId);
                
                bot_support('Новое сообщение от пользователя '.$user_info[0]['first_name'].' '.$user_info[0]['last_name'].':
'.$incommingText.'

Для ответа перейдите по ссылке: https://'.BOT_HOST.'adm/?f=dialog&vkId='.$vkId."&botId={$currentGroupId}");
               }
               
            }
            else
            {
            
            $attachments=array();
            $buttons=array();
            $sql=mysqli_query($dblink,"SELECT replyText,php FROM `".DBP."botReply` WHERE vkGroupId IN ({$currentGroupId},-1) AND payloadText='{$payloadText}' ORDER BY vkGroupId DESC");
            
            $sql_default=mysqli_query($dblink,"SELECT replyText,php FROM `".DBP."botReply` WHERE vkGroupId IN ({$currentGroupId},-1) AND payloadText='default' ORDER BY vkGroupId DESC");
            
            
            
            if(mysqli_num_rows($sql))
               list($replyText,$php)=mysqli_fetch_array($sql);
            
            elseif(mysqli_num_rows($sql_default))
            {
               list($replyText,$php)=mysqli_fetch_array($sql_default);
               bot_debugger("Неизвестный payload {$payloadText} для группы {$currentGroupId} отправлен {$vkId} vk.com/id{$vkId}, emulate: {$_GET['emulate']}. MY IP: ".gethostbyname($_SERVER['SERVER_NAME']).", REMOTE IP: {$_SERVER['REMOTE_ADDR']}
               Отправлено сообщение по умолчанию:
               
               {$replyText}");
               
               log_msg("Неизвестный payload {$payloadText} для группы {$currentGroupId} отправлен {$vkId} vk.com/id{$vkId}, emulate: {$_GET['emulate']}. MY IP: ".gethostbyname($_SERVER['SERVER_NAME']).", REMOTE IP: {$_SERVER['REMOTE_ADDR']}
               Отправлено сообщение по умолчанию:
               
               {$replyText}");
            }
            else
            {
               bot_debugger("Неизвестный payload {$payloadText} для группы {$currentGroupId} отправлен {$vkId} vk.com/id{$vkId}, emulate: {$_GET['emulate']}. MY IP: ".gethostbyname($_SERVER['SERVER_NAME']).", REMOTE IP: {$_SERVER['REMOTE_ADDR']}");
               break;
            }
            
            
            
            
            
            $userState='';
            
            $sql=mysqli_query($dblink,"SELECT vkGroupPHP FROM `".DBP."vkApi` WHERE vkGroupId={$currentGroupId}");
            list($vkGroupPHP)=mysqli_fetch_array($sql);
            
            $sql_default=mysqli_query($dblink,"SELECT vkGroupPHP FROM `".DBP."vkApi` WHERE vkGroupId=-1");
            list($vkGroupPHP_default)=mysqli_fetch_array($sql_default);
            
            if($vkGroupPHP)
                eval ($vkGroupPHP);
            elseif($vkGroupPHP_default)
                eval ($vkGroupPHP_default);
            eval ($php);
            
            mysqli_query($dblink,"DELETE FROM `".DBP."userState` WHERE vkId={$vkId} AND vkGroupId IN (0,{$currentGroupId})");
            
            //bot_debugger("INSERT INTO `".DBP."userState` (vkId,payloadText,vkGroupId) VALUES ({$vkId},'{$userState}',{$currentGroupId})");
            
            if($userState)
               mysqli_query($dblink,"INSERT INTO `".DBP."userState` (vkId,payloadText,vkGroupId) VALUES ({$vkId},'{$userState}',{$currentGroupId})");
            
            $txt=personal_text($vkId, $replyText);
            vkApi_messagesSendButtons($vkId, $txt[$vkId],array_merge($buttons,$buttons_def),$attachments);
            
            }
            
          break;
        }

?>
