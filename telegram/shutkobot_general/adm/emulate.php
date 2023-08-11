<?php
require_once(dirname(__FILE__).'/functions.php');
require_once(dirname(__FILE__).'/login.php');

$page_title='Эмуляция запроса пользователя';

require_once(dirname(__FILE__).'/template/top.php');

if (isset ($_POST['vkGroupId']))  //Обработчик сохранения формы
{
   
   $sql=mysqli_query($dblink,"SELECT secret FROM `".DBP."vkApi` WHERE vkGroupId={$_POST['vkGroupId']}");
   
   list($secret)=mysqli_fetch_array($sql);
   
   $vkId=get_vk_ids($_POST['vkId'])[0];
   
   $json='{"type":"message_new","object":{"date":0,"from_id":'.$vkId.',"id":0,"out":0,"peer_id":'.$vkId.',"text":"'.$_POST['incommingText'].'","conversation_message_id":0,"fwd_messages":[],"important":false,"random_id":0,"attachments":[]';
   
   if ($_POST['payloadText'])
      $json.=',"payload":"{\"payload\":\"'.$_POST['payloadText'].'\"}"';
   
   $json.=',"is_hidden":false},"group_id":'.$_POST['vkGroupId'].',"secret":"'.$secret.'"}';
   
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
    $groupsList.= "<option value=\"{$vkGroupId}\"{$selected}>{$result[0]['name']}";
    
    $groupName[$vkGroupId]=$result[0]['name'];
}

?>

<form action="" method="post">
<p>Группа: <select name="vkGroupId"><?php echo $groupsList; ?></select><br>
Пользователь: <input type="text" name="vkId" value="<?php if(isset($_POST['vkId'])) echo $_POST['vkId']; ?>" /><br>
Сообщение: <input type="text" name="incommingText" value="<?php if(isset($_POST['incommingText'])) echo $_POST['incommingText']; ?>" /><br>
Payload: <input type="text" name="payloadText" value="<?php if(isset($_POST['payloadText'])) echo $_POST['payloadText']; ?>" /><br>
<input type="submit" value="Эмитировать запрос" /><br></p>
</form>

<?

require_once(dirname(__FILE__).'template/bottom.php');

?>
