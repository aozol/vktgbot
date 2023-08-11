<?php
require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/login.php');

$page_title='Эмуляция запроса пользователя';

require_once(dirname(__FILE__).'/template/top.php');

if (isset ($_POST['vkGroupId']))  //Обработчик сохранения формы
{
   
   $sql=mysqli_query($dblink,"SELECT secret FROM `".DBP."vkApi` WHERE vkGroupId={$_POST['vkGroupId']}");
   
   list($secret)=mysqli_fetch_array($sql);
   
   $vkIds=get_vk_ids($_POST['vkId']);
   
   foreach ($vkIds as $vkId)
   {
   
        $user_info=vkApi_usersGet($vkId);

        $json='{"update_id":0,
"callback_query":{"id":"0","from":{"id":'.$vkId.',"is_bot":false,"first_name":"'.$user_info[0]['first_name'].'","last_name":"'.$user_info[0]['last_name'].'","username":"'.$user_info[0]['username'].'","language_code":"ru"},"message":{"message_id":608,"from":{"id":'.$vkId.',"is_bot":true,"first_name":"","username":"psy_result_bot"},"chat":{"id":'.$vkId.',"first_name":"","last_name":"","username":"","type":"private"},"date":'.time().',"text":"'.$_POST['incommingText'].'","reply_markup":{"inline_keyboard":[[{"text":".","callback_data":"'.$_POST['payloadText'].'"}]]}},"chat_instance":"3355731505611132218","data":"'.$_POST['payloadText'].'"}}';
        
        
        
        
        
        
        
        
        $query=http_build_query([$json]);
        $url = 'http://'.BOT_HOST.'chat_processor.php?emulate=1';
        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL,$url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $query);
        
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curl);
        $error = curl_error($curl);
        if ($error) {
            log_error($error);
            //throw new Exception("Failed {$method} request");
            return $error;
        }
        curl_close($curl);
   
   }
      
}

if ($adm_info['root'])
    $sql=mysqli_query($dblink,"SELECT vkGroupId,token FROM `".DBP."vkApi`");
else
    $sql=mysqli_query($dblink,"SELECT vkGroupId,token FROM `".DBP."vkApi` WHERE admin={$adm_info['id']}");
    
$groupsList.= "";
   
while (list($vkGroupId,$token)=mysqli_fetch_array($sql))
{
    if($vkGroupId==$_POST['vkGroupId'])
       $selected=' selected';
    else
       $selected='';
    
    $params['group_id'] = $vkGroupId;
    $result=_vkApi_call('groups.getById', $params);
    $groupsList.= "<option value=\"{$vkGroupId}\"{$selected}>{$vkGroupId}";
    
    $groupName[$vkGroupId]=$vkGroupId;
}

?>

<form action="" method="post">
<p>Группа: <select name="vkGroupId"><?php echo $groupsList; ?></select><br>
Пользователи (по 1 на строке или через запятую): <textarea name="vkId"><?php if(isset($_POST['vkId'])) echo $_POST['vkId']; ?></textarea><br>
Сообщение: <input type="text" name="incommingText" value="<?php if(isset($_POST['incommingText'])) echo $_POST['incommingText']; ?>" /><br>
Payload: <input type="text" name="payloadText" value="<?php if(isset($_POST['payloadText'])) echo $_POST['payloadText']; ?>" /><br>
<input type="submit" value="Эмитировать запрос" /><br></p>
</form>

<?

require_once(dirname(__FILE__).'/template/bottom.php');

?>
