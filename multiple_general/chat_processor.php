<?php




if (!isset($_REQUEST)) {
    return;
}

require_once(dirname(__FILE__).'/functions.php');

//Получаем и декодируем уведомление

if (isset($_GET['emulate'])) //если надо эмулировать действия пользователя, то скрипт передает информацию сам себе, а не ждет от пользователя - соответственно считываем ее в другом формате
{
   //log_msg(gethostbyname($_SERVER['SERVER_NAME']).' '.$_SERVER['REMOTE_ADDR']);
   //if(gethostbyname($_SERVER['SERVER_NAME'])!=$_SERVER['REMOTE_ADDR']) exit;//запрос только от себя
   	
   
   $data = json_decode($_POST[0]);
   
   //log_msg(print_r($data,true));
}
else
   $data = json_decode(file_get_contents('php://input'));



$currentGroupId=$data->group_id; //группа, с которой взаимодействует пользователь

$sql=mysqli_query($dblink,"SELECT token,confirmToken,secret FROM `".DBP."vkApi` WHERE vkGroupId={$currentGroupId}"); //подгружаем из базы данные по текущей группе
list($token,$confirmationToken,$secretKey)=mysqli_fetch_array($sql);
//log_msg(print_r($data,true));

// проверяем secretKey
if(strcmp($data->secret, $secretKey) !== 0 && strcmp($data->type, 'confirmation') !== 0)
    return;

//Проверяем, что находится в поле "type"
switch ($data->type) {
    //Если это уведомление для подтверждения адреса с$userIdервера...
    case 'confirmation':
        //...отправляем строку для подтверждения адреса
        
        
        
        
        echo $confirmationToken;
        break;

    //Если это уведомление о новом сообщении...
    case 'message_new':
        //...получаем id его автора и текст сообщения
        
        
          
        
        $vkId = $data->object->message->peer_id;
                
        $incommingText = trim($data->object->message->text);
        
        if(isset($data->object->message->payload)) //если кнопка - то получаем скрытую нагрузку кнопки
        {
          $pl=json_decode($data->object->message->payload);
          $payloadText = $pl->payload;
        }
          
        else
          $payloadText = '';
        
        //bot_debugger("Start payload: {$payloadText}");
          

        if (($incommingText=='Начать') OR ($incommingText=='Start') OR ($incommingText=='начать') OR ($incommingText=='start') ) //Первое сообщение
        {
          //bot_debugger("Start payload: {$payloadText}");
          $payloadText = 'start';
        }
        
        if (($incommingText=='test_by_admin')) //заглушка для тестов
        {
          //bot_debugger("Start payload: {$payloadText}");
          $payloadText = 'test_by_admin';
        }
        

        
        if (user_auth($vkId)) //Нового пользователя - подписываем на рассылку и отправляем первое сообщение что бы он там ни написал
        {
          //bot_debugger("Start payload: {$payloadText}");
          $payloadText = 'start';
        }
        
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
          
           case "menu": // если пользователь не видит кнопок и хочет общаться текстом, то отправляем список команд
            
             $sql=mysqli_query($dblink,"SELECT buttonText FROM `".DBP."keyboard` WHERE vkId={$vkId}");
             
             if(!mysqli_num_rows($sql))
                bot_debugger("Ошибка вызова меню для {$vkId}");
            else
            {
                $text="Для использования команды вместо кнопки просто скопируйте и отправьте текст команды в ответ. Вам на данный момент доступны следующие команды:";
                
                vkApi_messagesSend($vkId, $text);
                
                while (list($buttonText)=mysqli_fetch_array($sql))
                    vkApi_messagesSend($vkId, $buttonText);
            }
             
             
             user_mlist_manage($vkId,$payloadParams,1);
             
             $text='Вы успешно подписаны на рассылку: "'.$mlistName[$payloadParams].'"!';
             
             
          
          break;
           
           
           case "subscription": //кнопки управления подпиской
             
             if (isset($payloadParams))
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
                    if (!isset($subscription_buttons[$i]))
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
          
          case "sub": //подписка на рассылку
            
             $sql=mysqli_query($dblink,"SELECT mlistId,mlistName FROM `".DBP."mlists`");
             while (list($mlistId,$name)=mysqli_fetch_array($sql))
                $mlistName[$mlistId]=$name;
             
             user_mlist_manage($vkId,$payloadParams,1);
             
             $text='Вы успешно подписаны на рассылку: "'.$mlistName[$payloadParams].'"!';
             
             vkApi_messagesSendButtons($vkId, $text,array_merge($buttons,$buttons_def));
          
          break;
          
          case "unsub": //отписка от рассылки
          
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
          
          case 'get_promo': //получение индивидуального промокода
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
          
          default: //другие варианты - берем из базы реакцию
          
            if (!$payloadText)
            {
                              
               // Проверка, является ли сообщение промо-кодом
               $sql=mysqli_query($dblink,"SELECT finDate, activationsN,activatedN, actionPHP FROM `".DBP."promocodes` WHERE LOWER(promoCode)='".mb_strtolower($incommingText)."' ORDER BY finDate DESC");
            
               if(list($finDate, $activationsN,$activatedN, $actionPHP)=mysqli_fetch_array($sql))
               {
                   
                   $promocodeWorks=0;
                   
                   if($finDate<date('Y-m-d'))
                   {
                      $replyText='Cрок действия данного промо-кода истек - попробуйте использовать другой. Чтобы связаться с администратором по любым вопросам, просто ответьте на это сообщение.';
                   }
                   
                   elseif(($activationsN)&($activationsN<=$activatedN))
                   {
                      $replyText='Данный промо-код уже был активирован максимально возможное число раз - попробуйте использовать другой. Чтобы связаться с администратором по любым вопросам, просто ответьте на это сообщение.';
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
        
        

        //Возвращаем "ok" серверу Callback API
        echo('ok');

        break;

        
        default:
        echo('ok');

        break;
        
}
?>
